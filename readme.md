#Login con redes sociales

Laravel nos permite trabajar el login con redes sociales de forma muy simple, esto se logra a través de Socialite.

Socialite ofrece autenticación OAuth con Facebook, Twitter, Google, LinkedIn, GitHub y Bitbucket.

Puedes ver más información de Socialite aquí:

- [Documentación Oficial](https://laravel.com/docs/5.0/authentication#social-authentication).
- [Github](https://github.com/laravel/socialite).

Antes de meternos de lleno en Laravel, debemos crear las apps necesarias de cada red social a utilizar, sin embargo, en este tutorial no veremos ese punto, asumiremos que ya tenemos todo lo necesario. Esto es debido a que existe mucha información sobre esto.

Obviamente, lo primero es crear un proyecto

```shell
laravel new project
```

Ahora, ingresamos al repositorio y agregamos el package de socialite

```shell
cd <proyecto>
composer require laravel/socialite
```

Vamos a **/config/app.php** y buscamos el arreglo de providers, aquí agregamos:

```php
Laravel\Socialite\SocialiteServiceProvider::class,
```

```php
'Socialite' => Laravel\Socialite\Facades\Socialite::class,
```

Antes de seguir configurando Socialite, lo que haremos es modificar la migración de usuario, esto es con el único fin de guardar el id de la red social y que el email no sea único

```php
public function up()
{
    Schema::create('users', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->string('social_id');
        $table->rememberToken();
        $table->timestamps();
    });
}
```

Luego, vamos a nuestro archivos de entorno (**.env**) y configuramos las variables de base de datos y creamos las sociales a utilizar

```
DB_CONNECTION=sqlite
 
FB_CLIENT_ID=<client_id>
FB_SECRET_ID=<secret_id>
FB_REDIRECT=http://localhost:8000/auth/facebook/callback
 
GP_CLIENT_ID=<client_id>
GP_SECRET_ID=<secret_id>
GP_REDIRECT=http://localhost:8000/auth/google/callback
```

En este ejemplo utilizamos sqlite, pero obviamente podemos trabajar con MySQL u otro motor.

Ejecutamos migración

```shell
php artisan migrate
```

Ahora tenemos que ir a **config/services.php** para agregar los valores

```php
'facebook' => [
    'client_id' => env('FB_CLIENT_ID'),
    'client_secret' => env('FB_SECRET_ID'),
    'redirect' => env('FB_REDIRECT'),
],
 
'google' => [
    'client_id' => env('GP_CLIENT_ID'),
    'client_secret' => env('GP_SECRET_ID'),
    'redirect' => env('GP_REDIRECT'),
],
```

Luego, vamos al modelo **User.php** y modificamos fillable para poder guardar el social_id

```php
protected $fillable = [
    'name', 'email', 'password', 'social_id'
];
```

Creamos el controlador necesario para manejar el login a través de las redes sociales


```shell
php artisan make:controller SocialController
```

Este controlador posee la siguiente lógica

```php
<?php
 
namespace App\Http\Controllers;
 
use App\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
 
class SocialController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }
 
    public function callback($provider)
    {
        // Obtener información de usuario desde provider
        $userProvider = Socialite::driver($provider)->user();
 
        //Validar que el usuario exista
        $user = User::where([
            ['email', '=', $userProvider->email],
            ['social_id', '=', $userProvider->id]
        ])->first();
 
        if (!$user) {
 
            $insert = User::create([
                'name' => $userProvider->name,
                'email' => $userProvider->email,
                'password' => bcrypt(
                    substr(
                        str_shuffle(
                            str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)
                        ), 0, 5)
                ),
                'social_id' => $userProvider->id
            ]);
 
            $user = $insert;
        }
 
        // Hacer login de usuario
        Auth::login($user);
 
        return redirect('/');
    }
 
    public function sign_out()
    {
        Auth::logout();
        return redirect('/');
    }
}
```

Configurado el controlador, debemos asignar las rutas, para esto vamos a /routes/web.php y agregamos:

```php
Route::get('/auth/{provider}/redirect', [
    'as' => 'social_redirect',
    'uses' => 'SocialController@redirect'
]);
 
Route::get('/auth/{provider}/callback', [
    'as' => 'social_handle',
    'uses' => 'SocialController@callback'
]);
 
Route::get('/auth/sign_out', [
    'as' => 'sign_out',
    'uses' => 'SocialController@sign_out'
]);
```

Para hacer más simple la edición de la vista, utilizaremos la que viene de forma predeterminada (**welcome.blade.php**) y buscamos el div links

```html
<div class="links">
    <a href="https://laravel.com/docs">Documentation</a>
    <a href="https://laracasts.com">Laracasts</a>
    <a href="https://laravel-news.com">News</a>
    <a href="https://forge.laravel.com">Forge</a>
    <a href="https://github.com/laravel/laravel">GitHub</a>
</div>
```

Lo reemplazamos por esto:

```html
<div class="links">
    <a href="{{ route('social_redirect', ['provider' => 'facebook']) }}">Login Facebook</a>
    <a href="{{ route('social_redirect', ['provider' => 'google']) }}">Login Google</a>
    <a href="{{ route('sign_out') }}">Salir</a>
</div>
```

Este tutorial no es lo más optimizado para manejar los usuarios, está hecho de esta forma para poder entenderlo fácilmente.

- [Demo](http://fierce-castle-88699.herokuapp.com/).
- [Repositorio](https://github.com/normeno/laravel5p3-socialite).