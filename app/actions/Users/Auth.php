<?php

namespace Actions\Users;

class Auth extends \Base\Action
{
    /**
     * $params includes an email and password. tries to log the user
     * in and sets the session/cookie.
     *
     * @param array $params
     * @return bool
     */
    public function login( $params )
    {
        $validate = $this->getService( 'validate' );
        $validate->add(
            'email',
            array(
                'exists' => array(),
                'email' => array()
            ));
        $validate->add(
            'password',
            array(
                'exists' => array()
            ));

        if ( ! $validate->run( $params ) )
        {
            return FALSE;
        }

        // authorize the email/password combo
        //
        $user = $this->authorizeLogin(
            $params[ 'email' ],
            $params[ 'password' ] );

        if ( ! valid( get( $user, 'id' ) ) )
        {
            return FALSE;
        }

        // save the session data
        //
        $this->getService( 'session' )->set( 'user_id', $user->id );
        $this->getService( 'auth' )->load( $user->id );

        // write out the cookie token
        //
        return self::createToken( $user->id );
    }

    /**
     * Authorize an email/password
     *
     * @param string $email
     * @param string $password
     * @return object | bool
     */
    public function authorizeLogin( $email, $password )
    {
        // check if the email exists
        //
        $util = $this->getService( 'util' );
        $user = \Db\Sql\Users::findByEmail( $email )->getFirst();

        if ( ! $user
            || ! valid( $user->email, STRING ) )
        {
            $util->addMessage( 'Email and password do not match', ERROR );
            return FALSE;
        }

        // hash the plaintext password and compare it against the
        // database password.
        //
        $security = $this->getService( 'security' );

        if ( ! $security->checkHash( $password, $user->password ) )
        {
            $util->addMessage( 'Email and password do not match', ERROR );
            return FALSE;
        }

        return $user;
    }

    /**
     * Return a hashed version of a user's password
     *
     * @param string $password
     */
    public function hashPassword( $password )
    {
        $security = $this->getService( 'security' );

        return $security->hash( $password );
    }

    /**
     * Authorize a user's login token
     *
     * @return object | bool
     */
    public function authorizeToken()
    {
        $cookies = $this->getService( 'cookies' );

        // read the cookie, check if the token belongs to a user
        //
        if ( ! $cookies->has( 'token' ) )
        {
            return FALSE;
        }

        $token = $cookies->get( 'token' );

        if ( ! valid( $token->getValue(), STRING ) )
        {
            return FALSE;
        }

        // try to get the user by token
        //
        $user = \Db\Sql\Users::getByToken( $token );

        if ( ! $user || ! valid( $user->id ) )
        {
            return FALSE;
        }

        // save the session data
        //
        $session = $this->getService( 'session' );
        $session->set( 'user_id', $user->id );

        return $user;
    }

    /**
     * Creates a new cookie token, saves it for the requested user,
     * and writes the cookie.
     *
     * @param integer userId
     * @param bool $returnToken
     * @return bool | string
     */
    public function createToken( $userId, $returnToken = FALSE )
    {
        $config = $this->getService( 'config' );
        $cookies = $this->getService( 'cookies' );
        $util = $this->getService( 'util' );

        // set the cookie
        //
        $token = $this->generateRandomToken();
        $cookieSet = $cookies->set(
            'token',
            $token,
            time() + $config->cookies->expire,
            $config->cookies->path,
            $config->cookies->secure,
            $config->paths->hostname,
            $config->cookies->httpOnly );

        if ( ! $cookieSet )
        {
            $util->addMessage( 'Failed to save login cookie', ERROR );
            return FALSE;
        }

        // save the user setting 'cookie_token'
        //
        $setting = new \Db\Sql\Settings();
        $settingSaved = $setting->save(
            array(
                'object_id' => $userId,
                'object_type' => 'user',
                'key' => $config->settings->cookieToken,
                'value' => $token
            ));

        if ( ! $settingSaved )
        {
            $util->addMessage( 'Failed to save login token', ERROR );
            return FALSE;
        }

        return ( $returnToken )
            ? $token
            : TRUE;
    }

    /**
     * Unsets the cookie token
     *
     * @param integer $userId
     * @return bool
     */
    public function destroyToken( $userId = NULL )
    {
        $config = $this->getService( 'config' );
        $cookies = $this->getService( 'cookies' );
        $auth = $this->getService( 'auth' );

        $userId = ( $userId ) ? $userId : $auth->getUserId();
        $setting = \Db\Sql\Settings::get(
            $userId,
            'user',
            $config->settings->cookieToken,
            array(
                'first' => TRUE
            ));

        return ( $setting
            && $setting->delete()
            && $cookies->get( 'token' )->delete() );
    }

    /**
     * Kills the session
     *
     * @return bool
     */
    public function destroySession( $purge = TRUE )
    {
        $session = $this->getService( 'session' );
        $session->remove( 'user_id' );

        $auth = $this->getService( 'auth' );
        $auth->destroy();

        return ( $purge )
            ? $session->destroy()
            : TRUE;
    }

    /**
     * Generates a crytographically secure, random token
     *
     * @param integer $length
     * @return string
     */
    public function generateRandomToken( $length = 40 )
    {
        $token = "";
        $code_alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code_alphabet .= "abcdefghijklmnopqrstuvwxyz";
        $code_alphabet .= "0123456789";
        $alphabet_length = strlen( $code_alphabet );

        for( $i = 0; $i < $length; $i++ )
        {
            $token .= $code_alphabet[ $this->cryptoRandSecure( 0, $alphabet_length ) ];
        }

        return $token;
    }

    /**
     * Generates a crytographically secure, random number
     *
     * @param integer $min
     * @param integer $max
     * @return long
     */
    public function cryptoRandSecure( $min, $max )
    {
        $range = $max - $min;

        if ( $range < 0 )
        {
            return $min; // not so random...
        }

        $log = log( $range, 2 );
        $bytes = (int) ( $log / 8 ) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1

        do
        {
            $rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
            $rnd = $rnd & $filter; // discard irrelevant bits
        }
        while ( $rnd >= $range );

        return $min + $rnd;
    }
}
