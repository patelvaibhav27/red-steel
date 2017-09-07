<?php

namespace App\Api\V1\Controllers;

use JWTAuth;
use Excel;
use App\Book;
use App\Book_cat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookController extends Controller
{
    use Helpers;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $data = Book::get();
        foreach($data as $key => $value){
            $value->book_cat = $value->cat->name;
        }
        return $data;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $currentUser = JWTAuth::parseToken()->authenticate();
        $book = $currentUser->books()->find($id);
        if(!$book)
            throw new NotFoundHttpException;
        return $book;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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
}
