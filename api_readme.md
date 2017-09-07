#Creating the API

```
$ php artisan make:migration create_books_table --create=books
```
a new file will be created in database/migrations. We then add the following fields in the up() method.
```
public function up()
{
    Schema::create('books', function (Blueprint $table) {
        $table->increments('id');

		// adding specific fields here...
        $table->string('title');
        $table->string('author_name');
        $table->integer('pages_count');
        $table->text('img')->nullable();
		$table->integer('book_cats_id')->index();

        $table->integer('user_id')->index();
        $table->timestamps();
    });
}
```
Create book category table
```
$ php artisan make:migration create_book_cats_table --create=book_cats
```
a new file will be created in database/migrations. We then add the following fields in the up() method.
```
public function up()
{
    Schema::create('books', function (Blueprint $table) {
        $table->increments('id');

		// adding specific fields here...
        $table->string('name');
        $table->timestamps();
    });
}
```
Time to “migrate” again, by typing
```
$ php artisan migrate
```
to add the new table to the schema. Now, let’s create the model, with
```
$ php artisan make:model Book
$ php artisan make:model Book_cat
```
and we are done. Due to the conventions in Laravel, the Book model already “knows” that it has to work with the books table we have created. Here’s the model:
```
namespace App;
use Illuminate\Database\Eloquent\Model;
class Book extends Model
{
    protected $fillable = ['title', 'author_name', 'pages_count'];
    // public $table = "books"; // public $table = "<table-name>" ;
    public function cat(){
        return $this->belongsTo('App\Book_cat','book_cats_id');
    }
}
```
book cat table
```
namespace App;
use Illuminate\Database\Eloquent\Model;
class Book_cat extends Model
{
    public $table = "book_cats";
}

```
Now, we have to edit our User model in order to define the relationship we will need to retrieve their related books. In app\User.php we add the following method:
```
public function books(){
    return $this->hasMany('App\Book');
}


```
The last step is the resource controller for the Book entity. Let’s type
```
$ php artisan make:controller BookController -r
```
**Note** : first Controllers create in **app/Api/Http/Controllers/Controllers-file-name** move to **app/Api/V1/Controllers/Controllers-file-name**
We also change the namespace:
```
namespace App\Api\V1\Controllers;
```
where App is the basic namespace we have chosen for our application. By default, we can leave it as it is.

Before proceeding, we have to remember to add some use directives at the beginning of the file:
```
use JWTAuth;
use Excel;
use App\Book;
use App\Book_cat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
```
We must also remember to include this Helpers trait in the controller:
```
class BookController extends Controller
{
    use Helpers;
...
```
Now we can implement our methods:

- index, to get the books list of a certain user;
- show, to get a single book given its id;
- store, to store a newly created book in the database;
- update, to store updates on a certain book, given its id;
- destroy, to delete an existing book from the database, given its id;

Let’s start from the first: index().
```
public function index()
{
    $data = Book::get();
    foreach($data as $key => $value){
        $value->book_cat = $value->cat->name;
    }
    return $data;
}
```
We can make a step forward, to store()
```
public function store(Request $request)
{
    $currentUser = JWTAuth::parseToken()->authenticate();
    $book = new Book;

    $book->title = $request->get('title');
    $book->author_name = $request->get('author_name');
    $book->pages_count = $request->get('pages_count');
    $book->book_cats_id = $request->get('book_cats_id');

    if( $request->hasFile('img')){
        $img = $request->file('img');
        $imageName = time().'.'.$img->getClientOriginalExtension();
        $img->move(storage_path('img'), $imageName);
    } else {
        //dd('No image was found');
        return response()->json([
            'status' => 'ok',
            'msg' => 'Image was not found '
        ], 404);
    }
    $book->img = $imageName;
    if($currentUser->books()->save($book))
        //return $this->response->created();
        return response()->json([
            'status' => 'ok',
            'msg' => 'Book added'
        ], 201);
    else
        return $this->response->error('could_not_create_book', 500);
}
```
We can finish the job by implementing the three remaining methods: show, update and destroy.
```
public function show($id)
{
    $currentUser = JWTAuth::parseToken()->authenticate();
    $book = $currentUser->books()->find($id);
    if(!$book)
        throw new NotFoundHttpException;
    return $book;
}

public function update(Request $request, $id)
{
    $currentUser = JWTAuth::parseToken()->authenticate();
    $book = $currentUser->books()->find($id);
    if(!$book)
        throw new NotFoundHttpException;
    $book->fill($request->all());
    if($book->save())
        //return $this->response->noContent();
        return response()->json([
            'status' => 'ok',
            'msg' => 'Book updated'
        ], 201);
    else
        return $this->response->error('could_not_update_book', 500);
}

public function destroy($id)
{
    $currentUser = JWTAuth::parseToken()->authenticate();
    $book = $currentUser->books()->find($id);
    if(!$book)
        throw new NotFoundHttpException;
    if($book->delete())
        //return $this->response->noContent();
        return response()->json([
            'status' => 'ok',
            'msg' => 'Book deleted'
        ], 201);
    else
        return $this->response->error('could_not_delete_book', 500);
}
```
Let’s do some tests before finishing our controller. Add the following code into routes/api.php:
```
//book route
$api->group(['middleware' => 'jwt.auth'], function ($api) {
    $api->get('books', 'App\\Api\\V1\\Controllers\\BookController@index');
    $api->get('books/{id}', 'App\\Api\\V1\\Controllers\\BookController@show');
    $api->post('books', 'App\\Api\\V1\\Controllers\\BookController@store');
    $api->put('books/{id}', 'App\\Api\\V1\\Controllers\\BookController@update');
    $api->delete('books/{id}', 'App\\Api\\V1\\Controllers\\BookController@destroy');
});
```