# Laravel Artisan Commands

## ການຕິດຕັ້ງແລະການສ້າງໂຄງການ
```bash
# ສ້າງໂຄງການໃໝ່
composer create-project laravel/laravel project-name

# ເປີດ server
php artisan serve

# ສ້າງ key ສຳລັບແອັບ
php artisan key:generate
```

## ການສ້າງ Files
```bash
# ສ້າງ Controller
php artisan make:controller UserController
php artisan make:controller UserController --resource

# ສ້າງ Model
php artisan make:model User
php artisan make:model User -m    # ພ້ອມກັບ migration

# ສ້າງ Migration
php artisan make:migration create_users_table

# ສ້າງ Seeder
php artisan make:seeder UserSeeder

# ສ້າງ Factory
php artisan make:factory UserFactory

# ສ້າງ Middleware
php artisan make:middleware CheckAge

# ສ້າງ Request
php artisan make:request StoreUserRequest
```

## Database Operations
```bash
# ດຳເນີນການ migration
php artisan migrate
php artisan migrate:rollback
php artisan migrate:refresh
php artisan migrate:fresh

# ດຳເນີນການ seeding
php artisan db:seed
php artisan db:seed --class=UserSeeder
```

## Cache Operations
```bash
# ລ້າງ cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

# Routing Examples
```php
// Basic Routes
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Resource Route
Route::resource('users', UserController::class);

// Route with middleware
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Route with prefix
Route::prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'users']);
});
```

# Model Relationships
```php
// One to One
public function profile()
{
    return $this->hasOne(Profile::class);
}

// One to Many
public function posts()
{
    return $this->hasMany(Post::class);
}

// Many to Many
public function roles()
{
    return $this->belongsToMany(Role::class);
}

// Has Many Through
public function comments()
{
    return $this->hasManyThrough(Comment::class, Post::class);
}
```

# Query Builder Examples
```php
// Basic Queries
User::all();
User::find(1);
User::where('active', 1)->get();
User::where('age', '>', 18)->orderBy('name')->get();

// Relationships
$user->posts()->create([
    'title' => 'New Post'
]);

$user->posts()->where('active', 1)->get();

// Eager Loading
User::with('posts')->get();
User::with(['posts', 'profile'])->get();
```

# Blade Template Syntax
```php
// Variables
{{ $variable }}
{!! $htmlContent !!}

// Conditionals
@if($condition)
    // content
@elseif($otherCondition)
    // content
@else
    // content
@endif

// Loops
@foreach($items as $item)
    {{ $item->name }}
@endforeach

@while($condition)
    // content
@endwhile

// Layout
@extends('layouts.app')
@section('content')
    // content
@endsection

// Include
@include('partial.header')
@includeWhen($condition, 'partial.content')

// Components
<x-alert type="error" :message="$message"/>
@component('components.alert')
    @slot('title')
        Alert Title
    @endslot
@endcomponent
```

# Validation Rules
```php
$request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|confirmed',
    'age' => 'required|numeric|min:18',
    'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
]);
```

# Authentication
```php
// Login
if (Auth::attempt(['email' => $email, 'password' => $password])) {
    // Authentication passed
}

// Check auth
@auth
    // User is authenticated
@endauth

// Check guest
@guest
    // User is not authenticated
@endguest

// Get authenticated user
$user = Auth::user();
$id = Auth::id();
```