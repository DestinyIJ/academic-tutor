<?php

namespace App\Http\Controllers;

use App\Events\TeacherRequestedFor;
use App\Events\TeacherRequestedForCancelled;
use App\Models\ParentRequests;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentRequestController extends Controller
{
    /**
     * Get all Requests
     * 
     * @return \Illuminate\Http\JsonResponse
     */

    public function showAll()
    {
        $requests_by_parent = ParentRequests::all();
        return response()->json($requests_by_parent);
    } 
    
    /**
     * Request Teacher
     *
     * @param  integer $id
     * 
     * @return \Illuminate\Http\JsonResponse
     */

    public function requestTeacher($id)
    {
        $parent = Auth::user()->role === "parent" ? Auth::user() : null;

        if($parent) {
            $teacher = User::where('role', 'teacher')
                            ->where('id', $id)
                            ->first();
            if(!$teacher) {
                return response()->json([
                    'message' => 'User requested for is not a Teacher'
                ]);
            }

            $parent_request = new ParentRequests();

            $parent_request->parent_id = $parent->id;
            $parent_request->parent_name = $parent->name;
            $parent_request->parent_email = $parent->email;
            $parent_request->parent_phone = $parent->phone;

            $parent_request->teacher_id = $teacher->id;
            $parent_request->teacher_name = $teacher->name;
            $parent_request->teacher_email = $teacher->email;
            $parent_request->teacher_phone = $teacher->phone;

            if($parent_request->save()) {
                TeacherRequestedFor::dispatch($parent, $teacher);
                return response()->json($parent_request);
            }
        }

        return response()->json([
            'message' => 'This User is not a Parent'
        ]);
    }

    /**
     * Cancel Parent Request for Teacher
     *
     * @param  integer $id
     * 
     * @return \Illuminate\Http\JsonResponse
     */

    public function cancelRequestTeacher($id)
    {
        $parent = Auth::user()->role === "parent" ? Auth::user() : null;

        if($parent) {
            $teacher = User::where('role', 'teacher')
                            ->where('id', $id)
                            ->first();
            if(!$teacher) {
                return response()->json([
                    'message' => 'Teacher requested for is not valid'
                ]);
            }

            $parent_request = ParentRequests::where('parent_id',Auth::user()->id)
                                                ->where('teacher_id', $id)
                                                ->first();


            if($parent_request->delete()) {
                TeacherRequestedForCancelled::dispatch($parent, $teacher);
                return response()->json($parent_request);
            }
        }

        return response()->json([
            'message' => 'This User is not a parent'
        ]);
    }

    /**
     * Get all Requests for a Teacher
     *
     * @param  integer $id
     * 
     * @return \Illuminate\Http\JsonResponse
     */

    public function ShowRequestsForTeacher($id)
    {
        $requests_for_teacher = ParentRequests::where('teacher_id', $id)->get();
        return response()->json($requests_for_teacher);
    }

    /**
     * Get all Requests by a Parent
     *
     * @param  integer $id
     * 
     * @return \Illuminate\Http\JsonResponse
     */

    public function ShowRequestsByParent($id)
    {
        $requests_by_parent = ParentRequests::where('parent_id', $id)->get();
        return response()->json($requests_by_parent);
    }
}