<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::with('user')->latest()->paginate(10);
        return view('messages.index', compact('messages'));
    }

    public function create()
    {
        return view('messages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $message = auth()->user()->messages()->create($validated);

        return redirect()->route('messages.index')
            ->with('success', 'Message created successfully.');
    }

    public function show(Message $message)
    {
        if (!$message->is_read) {
            $message->markAsRead();
        }
        return view('messages.show', compact('message'));
    }

    public function edit(Message $message)
    {
        return view('messages.edit', compact('message'));
    }

    public function update(Request $request, Message $message)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $message->update($validated);

        return redirect()->route('messages.index')
            ->with('success', 'Message updated successfully.');
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return redirect()->route('messages.index')
            ->with('success', 'Message deleted successfully.');
    }
}
