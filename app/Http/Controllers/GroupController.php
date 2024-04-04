<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GroupController extends Controller
{
    protected $CommonController;

    public function __construct(CommonController $CommonController)
    {
        $this->CommonController = $CommonController;
    }

    public function getGroupsByOwnerId($userId)
    {
        return Group::whereOwnerId($userId)->get();
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
        $group = Group::withTrashed()->where('id', '=', $id)->first();

        if (!isset($group)) {
            return redirect()->route('home');
        }

        return Inertia::render('ViewGroup', ['group' => $group]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_title' => 'required|max:255',
            'group_photo' => 'nullable|image',
        ]);

        $request['owner_id'] = auth()->user()->id;
        $request['active'] = true;

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
