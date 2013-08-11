<?php

class UserController extends BaseController {

    protected $user;
    protected $group;

    /**
     * Load the injected models.
     * Setup access permissions.
     */
    public function __construct(Page $page, User $user, Group $group) {
        $this->page  = $page;
        $this->user  = $user;
        $this->group = $group;

        $this->setPermissions(array(
            'index'   => 'mod',
            'create'  => 'admin',
            'store'   => 'admin',
            'index'   => 'mod',
            'edit'    => 'admin',
            'update'  => 'admin',
            'destroy' => 'admin',
        ));

        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $users = $this->user->orderBy('first_name')->get(array('id', 'first_name', 'last_name', 'email'));
        return $this->viewMake('users.index', array('users' => $users));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        return $this->viewMake('users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        return 'user store';
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        $user = $this->user->find($id);
        $this->checkUser($user);

        return $this->viewMake('users.show', array('user' => $user));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        $user = $this->user->find($id);
        $this->checkUser($user);

        $groups = $this->group->get(array('id', 'name'));

        return $this->viewMake('users.edit', array('user' => $user, 'groups' => $groups));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        $input = array(
            'first_name' => Binput::get('first_name'),
            'last_name'  => Binput::get('last_name'),
            'email'      => Binput::get('email'),
        );

        $rules = array(
            'first_name' => 'required|min:2|max:32',
            'last_name'  => 'required|min:2|max:32',
            'email'      => 'required|min:4|max:32|email',
        );

        $val = Validator::make($input, $rules);
        if ($val->fails()) {
            return Redirect::route('users.edit', array('users' => $id))->withInput()->withErrors($val->errors());
        }

        $user = $this->user->find($id);
        $this->checkUser($user);

        $user->update($input);

        $groups = $this->group->get(array('id', 'name'));

        foreach($groups as $group) {
            if ($user->inGroup($group)) {
                if (Binput::get('group_'.$group->id) === 'off') {
                    $user->removeGroup($group);
                }
            } else {
                if (Binput::get('group_'.$group->id) === 'on') {
                    $user->addGroup($group);
                }
            }
        }

        Session::flash('success', 'The user has been updated successfully.');
        return Redirect::route('users.show', array('users' => $user->getId()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        $user = $this->user->find($id);
        $this->checkUser($user);

        $user->delete();

        Session::flash('success', 'The user has been deleted successfully.');
        return Redirect::route('users.index');
    }

    protected function checkUser($user) {
        if (!$user) {
            return App::abort(404, 'User Not Found');
        }
    }
}
