<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Todo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    public function createTodo(Request $request)
    {
        try {
            $user = Auth::user();
            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'completed'   => 'nullable|boolean',
                'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
            $todo = new Todo();
            $todo->user_id = $user->id;
            $todo->title = $validated['title'];
            $todo->description = $validated['description'] ?? '';
            $todo->completed = filter_var($request->input('completed'), FILTER_VALIDATE_BOOLEAN);
            $todo->save(); // save first so we have a real todo->id to name the file

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = 'avatar' . str_pad($todo->id, 2, '0', STR_PAD_LEFT) . '.' . $extension;
                // saves physical file to: storage/app/public/todos/avatar01.jpg
                $path = $file->storeAs('todos', $filename, 'public');

                // stores the web-accessible url: /storage/todos/avatar01.jpg
                $todo->image_url = Storage::url($path);
                $todo->save();
            }

            return response()->json([
                'message' => "Todo saved {$todo->title} successfully",
                'todo'    => $todo,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong. Please try again.',
                'error'   => $e->getMessage(), // remove in production
            ], 500);
        }
    }

    public function updateTodo(Request $request, $id)
    {
        try {
            $todo = Todo::findOrFail($id);
            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'completed'   => 'nullable|boolean',
                'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
            $todo->title = $validated['title'];
            $todo->description = $validated['description'] ?? '';
            $todo->completed = filter_var($request->input('completed'), FILTER_VALIDATE_BOOLEAN);
            if ($request->hasFile('image')) {
                if ($todo->image_url) {
                    $oldPath = str_replace('/storage/', '', parse_url($todo->image_url, PHP_URL_PATH));
                    Storage::disk('public')->delete($oldPath);
                }

                $file = $request->file('image');
                $filename = 'avatar' . str_pad($todo->id, 2, '0', STR_PAD_LEFT) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('todos', $filename, 'public');

                $todo->image_url = Storage::url($path);
            }
            $todo->save();
            return response()->json([
                'message' => "Todo {$todo->title} updated successfully",
                'todo'    => $todo,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Todo not found',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong. Please try again.',
                'error'   => $e->getMessage(), // remove this line in production
            ], 500);
        }
    }

    public function index()
    {
        $todos = Todo::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($todos, 200);
    }

    public function show($id)
    {
        $todo = Todo::find($id);
        if (!$todo) {
            return response()->json([
                'message' => 'Todo not found.'
            ], 404);
        }
        return response()->json($todo, 200);
    }

    public function deleteTodo($id)
    {
        $todo = Todo::findOrFail($id);
        $titlename = $todo->title;
        if ($todo->image_url) {
            $oldPath = str_replace('/storage/', '', parse_url($todo->image_url, PHP_URL_PATH));
            Storage::disk('public')->delete($oldPath);
        }
        $todo->delete();
        return response()->json([
            'message' => "Todo {$titlename} deleted successfully."
        ], 200);
    }


}