<?php

namespace App\Http\Controllers;

use App\Enums\GroupMemberStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller
{
    protected $CommonController, $UserController;

    public function __construct(CommonController $CommonController, UserController $UserController)
    {
        $this->CommonController = $CommonController;
        $this->UserController = $UserController;
    }

    public function getGroupsByOwnerId($userId)
    {
        return Group::whereOwnerId($userId)->get();
    }

    public function getGroupMembersByGroupId($groupId)
    {
        return GroupMember::whereGroupId($groupId)->get();
    }

    public function isGroupMemberWithUserIdExisting($groupId, $userId)
    {
        if (isset($userId)) {
            return GroupMember::withTrashed()
                ->whereGroupId($groupId)
                ->whereUserId($userId)
                ->exists();
        }

        return false;
    }

    public function isGroupMemberWithEmailExisting($groupId, $email)
    {
        if (isset($email)) {
            return GroupMember::withTrashed()
                ->whereGroupId($groupId)
                ->whereEmail($email)
                ->exists();
        }

        return false;
    }

    public function index(Request $request)
    {
        return Inertia::render('Groups', ['groups' => $this->getGroupsByOwnerId($request->user()->id)]);
    }

    public function view(Request $request)
    {
        $id = intval($request['id']);
        if ($id === 0) {
            return redirect()->route('404');
        }

        $group = Group::withTrashed()
            ->where('id', '=', $id)
            ->with(['groupMembers'])
            ->with('groupMembers.user')
            ->first();

        if (!isset($group)) {
            return redirect()->route('home');
        }

        return Inertia::render('ViewGroup', [
            'group' => $group,
            'groupMembers' => $group->groupMembers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_title' => 'required|max:255',
            'group_photo' => 'nullable|image',
        ]);

        $request['owner_id'] = auth()->user()->id;

        if (!empty($request['group_photo'])) {
            $path = $request->file('group_photo')->store('group-photos', 'private');
            $request['img_path'] = $path;
        }

        try {
            DB::beginTransaction();
            $group = Group::create($request->all());
            DB::commit();

            return redirect()->route('groups')
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'success')
                ->with('message', 'Group created successfully.')
                ->with('route', 'groups')
                ->with('id', $group->id);
        } catch (\Exception $e) {
            DB::rollBack();

            return Inertia::render('Groups', [
                'errorMessage' => 'Failed to create record: ' . $this->CommonController->formatException($e),
            ]);
        }
    }

    public function addMember(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'email' => 'required|email|max:255'
        ]);

        $request['status'] = GroupMemberStatusEnum::PENDING->value;
        $user = $this->UserController->getUserByEmail($request['email']);
        if (isset($user)) {
            $request['user_id'] = $user->id;

            if ($this->isGroupMemberWithUserIdExisting($request['group_id'], $user->id)) {
                throw ValidationException::withMessages(['email' => 'This user is already part of the group.']);
            }
        }

        if ($this->isGroupMemberWithEmailExisting($request['group_id'], $request['email'])) {
            throw ValidationException::withMessages(['email' => 'This user is already part of the group.']);
        }

        try {
            DB::beginTransaction();
            $group = GroupMember::create($request->all());
            DB::commit();

            return redirect()->back()
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'success')
                ->with('message', 'Group member added successfully.')
                ->with('route', 'groups.view')
                ->with('id', $group->id);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->CommonController->handleException($e);
        }
    }

    public function edit(Request $request)
    {
    }

    public function update(Request $request)
    {
    }

    public function deletePhoto(Request $request)
    {
        $group = Group::find($request['id']);

        if (isset($group)) {
            $isDeleted = $this->CommonController->deletePhoto($request['img_path']);
            if ($isDeleted || !$isDeleted && !empty($group->img_path)) {
                try {
                    DB::beginTransaction();
                    $group->update(['img_path' => null]);
                    DB::commit();

                    return redirect()->back()
                        ->with('show', true)
                        ->with('type', 'default')
                        ->with('status', 'success')
                        ->with('message', 'Photo removed successfully.');
                } catch (\Exception $e) {
                    DB::rollBack();

                    return $this->CommonController->handleException($e);
                }
            }
        }

        return redirect()->back()
            ->with('show', true)
            ->with('type', 'default')
            ->with('status', 'error')
            ->with('message', 'No image was deleted or group was not found.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:groups,id',
        ]);

        // Retrieve the ids
        $ids = $request->get('ids');

        DB::beginTransaction();

        try {
            // Delete the records
            $deletedCount = Group::destroy($ids);

            DB::commit();

            $context = 'groups';
            if ($deletedCount == 1) $context = 'group';

            return redirect()->back()
                ->with('show', true)
                ->with('type', 'default')
                ->with('status', 'success')
                ->with('message', $deletedCount . ' ' . $context . ' deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->CommonController->handleException($e, 'default', 'delete');
        }
    }
}
