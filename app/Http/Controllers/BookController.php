<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function BookController()
    {
    }
    public function index()
    {
        //$books = Book::all(); //Book::with('authors'); //para traer solo los libros y los id de de category y editorial. NO TRAE AUTHORS
        $books = Book::with('authors', 'category', 'editorial')->get(); //para traer los libros con el authors
        return [
            "error" => false,
            "message" => "Successfull query",
            "data" => $books
        ];
    }

    public function store(Request $request)
    {
        //trim() -> Elimina espacio en blanco (u otro tipo de caracteres) del inicio y el final de la cadena
        DB::beginTransaction();
        try {
            $existIsbn = Book::where('isbn', trim($request->isbn))->exists();
            if (!$existIsbn) {
                $book = new Book();
                $book->isbn = trim($request->isbn);
                $book->title = $request->title;
                $book->description = $request->description;
                $book->publish_date = Carbon::now();
                $book->category_id = $request->category["id"];
                $book->editorial_id = $request->editorial_id;
                $book->save();

                foreach ($request->authors as $item) {
                    $book->authors()->attach($item);
                }
                $bookId = $book->id;
                return [
                    "status" => true,
                    "message" => "your book has been created",
                    "data" => [
                        "book_id" => $bookId,
                        "book" => $book
                    ]
                ];
            } else {
                return [
                    "status" => false,
                    "message" => "The ISBN already exists",
                    "data" => []
                ];
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }


        /*
        DATOS DE POSTMAN = http://localhost:8000/api/book/store

        {
   "isbn": "013615250225",
   try {
    //code...
   } catcException $e $th) {
    DB:rollBack();
   }
   "title": "register data with C-E-A",
   "description": "Programming book",
    "category":
        {
         "id":1
        },
    "editorial_id": 1,
    "authors":[
        {
            "id":2
        },
        {
            "id":4
        }
        ]
    }

        */
    }

    //UPDATE
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $response = $this->getResponse();
            $book = Book::find($id);
            if ($book) {
                $isbnOwner = Book::where("isbn", $request->isbn)->first(); //validar que el isb sea el mismo
                if (!$isbnOwner || $isbnOwner->id == $book->id) {
                    $book->isbn = trim($request->isbn);
                    $book->title = $request->title;
                    $book->description = $request->description;
                    $book->publish_date = Carbon::now();
                    $book->category_id = $request->category["id"];
                    $book->editorial_id = $request->editorial_id;
                    //DELETE
                    foreach ($book->authors as $item) {
                        $book->authors()->detach($item->id);
                    }
                    $book->update();
                    //ADD
                    foreach ($request->authors as $item) {
                        $book->authors()->attach($item);
                    }
                    $book = Book::with('authors', 'category', 'editorial')->where("id", $id)->get();
                    $response["erros"] = false;
                    $response["message"] = "Your book has been updeted";
                    $response["data"] = $book;
                } else {
                    $request["message"] = "ISBN duplicated";
                }
            } else {
    
                $request["message"] = "404 not found";
            }
    
            return $response;
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }

       
    }

    public function show($id)
    {
        $response = [
            "error" => false,
            "message" => "your data has ben showed!",
            "data" => []
        ];
        $book = Book::with('authors', 'category', 'editorial')->where("id", $id)->get();
        if ($book) {

            $response["data"] = $book;
        } else {
            $response["error"] = true;
            $response["message"] = "404 not found";
        }
        return $response;
    }

    public function delete($id)
    {
        $response = [
            "error" => false,
            "message" => "your data has been deleted!",
            "data" => []
        ];
        $book = Book::with('authors', 'category', 'editorial')->find($id);

        if ($book) {
            //DELETE
            foreach ($book->authors as $item) {
                $book->authors()->detach($item->id);
            }
            $book->delete();
        } else {
            $response["error"] = true;
            $response["message"] = "404 not found";
        }
        return $response;
    }
    
}
