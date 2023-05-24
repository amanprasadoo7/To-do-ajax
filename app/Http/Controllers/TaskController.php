<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        try {
            $tasks = Task::all();
            // dd($tasks);
            return view('tasks.index', compact('tasks'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while retrieving tasks.']);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|unique:tasks|max:255',
            ]);

            $task = Task::create([
                'title' => $request->input('title'),
            ]);

            return response()->json(['task_id' => $task->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while creating the task.']);
        }
    }

    public function complete(Task $task)
    {
        try {
            $task->completed = !$task->completed;
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the task status.']);
        }
    }


    public function destroy(Task $task)
    {
        try {
            $task->delete();

            return response()->json(['success' => 'Task deleted']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the task.']);
        }
    }
}
