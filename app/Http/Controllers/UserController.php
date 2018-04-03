<?php

namespace App\Http\Controllers;

use App\Notifications\UserUpdated;
use App\Role;
use Auth;
use App\User;
use App\Http\Resources\UserCollection;
use Illuminate\Http\Request;
use App\Http\Resources\User as UserResource;

class UserController extends Controller
{
    const INDEX_WITH = [
        'team',
        'opportunities',
        'customFields',
        'roles',
        'settings',
    ];

    const SHOW_WITH = [
        'team',
        'opportunities',
        'customFields',
        'roles',
        'settings',
    ];

    public function index()
    {
        return new UserCollection(User::with(static::INDEX_WITH)->paginate());
    }

    /**
     * @param $id
     *
     * @return UserResource
     */
    public function show($id)
    {
        return new UserResource(User::with(static::SHOW_WITH)->find($id));
    }

    /**
     * @param Request $request
     * @param         $id
     *
     * @TODO: Move company update to Model mutators
     *
     * @return UserResource
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {
        /** @var User $user */
        $user = User::findOrFail($id);
        $data = $request->all();
        $customFields = $data['custom_fields'] ?? [];
        $settings = $data['settings'] ?? [];
        $roleId = $data['role_id'] ?? null;
        $roles = $data['roles'] ?? $user->roles()->get()->all();
        $password = $data['password'] ?? null;
        $secondPassword = $data['second_password'] ?? null;

        $roleIds = array_map(function($r) { return $r['id']; }, $roles);

        if ($roleId && !in_array($roleId, $roleIds)) {
            $roleIds[] = $roleId;
        }

        $user->roles()->sync($roleIds);
        $user->update($data);

        if ($password && $password === $secondPassword) {
            $user->password = \Hash::make($password);
            $user->save();
        }

        $settings = array_map(function ($setting) {
            return is_array($setting) ? json_encode($setting) : $setting;
        }, $settings);

        $user->setSettings($settings);
        $user->assignCustomFields($customFields);

        return $this->show($user->id);
    }

    /**
     * @param Request $request
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function store(Request $request)
    {
        return User::create($request->all());
    }

    /**
     * @param $id
     *
     * @return string
     * @throws \Exception
     */
    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return '';
    }
}
