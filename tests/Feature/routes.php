<?php

use Happytodev\Blogr\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

// Blog routes for testing
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Filament routes for browser testing
Route::middleware('web')->group(function () {
    // Login redirect for unauthenticated users
    Route::get('/login', function () {
        return redirect('/admin/login');
    })->name('login');

    // Admin login page
    Route::get('/admin/login', function () {
        return '
        <!DOCTYPE html>
        <html>
        <head><title>Sign In</title></head>
        <body>
            <h1>Sign In</h1>
            <form method="POST" action="/admin/login">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <div>
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit">Sign In</button>
            </form>
        </body>
        </html>';
    })->name('filament.admin.auth.login');

    // Authentication
    Route::post('/admin/login', function () {
        $credentials = request()->only('email', 'password');

        // For testing purposes, always authenticate test users
        $testEmails = ['admin@example.com', 'validation@example.com', 'workflow@example.com'];
        if (in_array($credentials['email'], $testEmails)) {
            $user = \Workbench\App\Models\User::where('email', $credentials['email'])->first();
            if ($user) {
                \Illuminate\Support\Facades\Auth::login($user);
                request()->session()->regenerate();
                return redirect('/admin');
            }
        }

        // Fallback to normal authentication
        $user = \Workbench\App\Models\User::where('email', $credentials['email'])->first();
        if ($user && \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            \Illuminate\Support\Facades\Auth::login($user);
            request()->session()->regenerate();
            return redirect('/admin');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    })->name('filament.admin.auth.authenticate');

    // Dashboard redirect (temporarily remove auth for testing)
    Route::get('/admin', function () {
        return redirect('/admin/tags');
    })->name('filament.admin.pages.dashboard');

    // Tags routes (temporarily remove auth for testing)
    Route::prefix('admin/tags')->group(function () {
        Route::get('/', function () {
            $tags = \Happytodev\Blogr\Models\Tag::all();
            $tagsHtml = '';
            foreach ($tags as $tag) {
                $tagsHtml .= "<tr><td>{$tag->name}</td><td>{$tag->slug}</td><td><a href='/admin/tags/{$tag->id}/edit'>Edit</a></td></tr>";
            }

            return '
            <!DOCTYPE html>
            <html>
            <head><title>Tags</title></head>
            <body>
                <h1>Tags</h1>
                <a href="/admin/tags/create">Create Tag</a>
                <table>
                    <thead>
                        <tr><th>Name</th><th>Slug</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        ' . $tagsHtml . '
                    </tbody>
                </table>
            </body>
            </html>';
        })->name('filament.admin.resources.tags.index');

        Route::get('/create', function () {
            return '
            <!DOCTYPE html>
            <html>
            <head><title>Create Tag</title></head>
            <body>
                <h1>Create Tag</h1>
                <form method="POST" action="/admin/tags">
                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                    <div>
                        <label for="name">Name:</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                    <div>
                        <label for="slug">Slug:</label>
                        <input type="text" name="slug" id="slug">
                    </div>
                    <button type="submit">Create</button>
                </form>
            </body>
            </html>';
        })->name('filament.admin.resources.tags.create');

        Route::post('/', function () {
            $data = request()->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
            ]);

            $tag = \Happytodev\Blogr\Models\Tag::create($data);
            return redirect('/admin/tags')->with('success', 'Tag created successfully');
        })->name('filament.admin.resources.tags.store');

        Route::get('/{record}/edit', function ($record) {
            $tag = \Happytodev\Blogr\Models\Tag::findOrFail($record);
            return '
            <!DOCTYPE html>
            <html>
            <head><title>Edit Tag</title></head>
            <body>
                <h1>Edit Tag</h1>
                <form method="POST" action="/admin/tags/' . $tag->id . '">
                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                    <input type="hidden" name="_method" value="PUT">
                    <div>
                        <label for="name">Name:</label>
                        <input type="text" name="name" id="name" value="' . $tag->name . '" required>
                    </div>
                    <div>
                        <label for="slug">Slug:</label>
                        <input type="text" name="slug" id="slug" value="' . $tag->slug . '">
                    </div>
                    <button type="submit">Save</button>
                </form>
            </body>
            </html>';
        })->name('filament.admin.resources.tags.edit');

        Route::put('/{record}', function ($record) {
            $tag = \Happytodev\Blogr\Models\Tag::findOrFail($record);

            $data = request()->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
            ]);

            $tag->update($data);
            return redirect('/admin/tags')->with('success', 'Tag updated successfully');
        })->name('filament.admin.resources.tags.update');
    });
});
