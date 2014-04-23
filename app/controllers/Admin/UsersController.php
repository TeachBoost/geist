<?php

namespace Controllers\Admin;

class UsersController extends \Base\Controller
{
    public function beforeExecuteRoute()
    {
        $this->checkLoggedIn = TRUE;
        $this->view->setMainView( 'admin' );

        // check if they have access
        //
        if ( ! $this->auth->user[ 'access_users' ] )
        {
            return $this->quit( "You don't have access to users!", INFO, 'admin/articles' );
        }

        return parent::beforeExecuteRoute();
    }

    /**
     * Shows a list of users.
     */
    public function indexAction()
    {
        // get all of the posts
        //
        $this->view->pick( 'admin/users/index' );
        $this->view->users = \Db\Sql\Users::find([ 'is_deleted = 0' ]);
        $this->view->backPage = '';
        $this->view->buttons = [ 'newUser' ];
    }

    /**
     * Create a new user and redirect to the edit page.
     */
    public function newAction()
    {
        // create the user
        //
        $action = new \Actions\Users\User();
        $userId = $action->create();

        // redirect
        //
        $this->redirect = "admin/users/edit/$userId";
    }

    /**
     * Edit a user
     */
    public function editAction( $userId = "" )
    {
        if ( ! valid( $userId, INT ) )
        {
            return $this->quit( "No user ID specified", INFO, 'admin/users' );
        }

        $user = \Db\Sql\Users::findFirst( $userId );

        if ( ! $user )
        {
            return $this->quit( "That user doesn't exist!", INFO, 'admin/users' );
        }

        $this->view->pick( 'admin/users/edit' );
        $this->view->user = $user;
        $this->view->backPage = 'admin/users';
        $this->view->subPage = 'Edit User';
        $this->view->buttons = [ 'saveUser' ];
    }

    /**
     * Save a user
     */
    public function saveAction()
    {
        // edit the user
        //
        $data = $this->request->getPost();
        $userAction = new \Actions\Users\User();
        $user = $userAction->edit( $data );
        $userId = $this->request->getPost( 'id' );

        if ( ! $user )
        {
            return ( valid( $userId ) )
                ? $this->quit( "", INFO, "admin/users/edit/{$userId}" )
                : $this->quit( "", INFO, 'admin/users' );
        }

        // redirect
        //
        $this->redirect = "admin/users/edit/{$user->id}";
    }

    /**
     * Delete a user
     */
    public function deleteAction( $id = "" )
    {
        $userAction = new \Actions\Users\User();
        $user = $userAction->delete( $id );

        $this->redirect = 'admin/users';
    }
}