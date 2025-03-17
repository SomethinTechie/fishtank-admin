<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index()
    {
        $feeds = Feed::with('user')->latest()->paginate(10);
        return view('feeds.index', compact('feeds'));
    }

    public function create()
    {
        return view('feeds.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image_url' => 'nullable|url',
            'is_published' => 'boolean'
        ]);

        $feed = auth()->user()->feeds()->create($validated);

        if ($feed->is_published) {
            $feed->publish();
        }

        return redirect()->route('feeds.index')
            ->with('success', 'Feed created successfully.');
    }

    public function show(Feed $feed)
    {
        return view('feeds.show', compact('feed'));
    }

    public function edit(Feed $feed)
    {
        return view('feeds.edit', compact('feed'));
    }

    public function update(Request $request, Feed $feed)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image_url' => 'nullable|url',
            'is_published' => 'boolean'
        ]);

        $feed->update($validated);

        if ($feed->is_published && !$feed->published_at) {
            $feed->publish();
        } elseif (!$feed->is_published && $feed->published_at) {
            $feed->unpublish();
        }

        return redirect()->route('feeds.index')
            ->with('success', 'Feed updated successfully.');
    }

    public function destroy(Feed $feed)
    {
        $feed->delete();

        return redirect()->route('feeds.index')
            ->with('success', 'Feed deleted successfully.');
    }

    public function publish(Feed $feed)
    {
        $feed->publish();

        return redirect()->route('feeds.index')
            ->with('success', 'Feed published successfully.');
    }

    public function unpublish(Feed $feed)
    {
        $feed->unpublish();

        return redirect()->route('feeds.index')
            ->with('success', 'Feed unpublished successfully.');
    }
}
