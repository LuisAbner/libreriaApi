<?php

namespace App\Http\Controllers;

use App\Models\BookReview;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookReviewController extends Controller
{
    public function addBookReview(Request $request){
        DB::beginTransaction();
        try {
            $book = new BookReview();
            $book->comment = trim($request->comment);
            $book->user_id = trim(auth()->user()->id);
            $book->book_id = trim($request->book_id);
            $book->edited =false;
            $book->save();    

            DB::commit();
            return $this->getResponse200($book);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500([$e->getMessage()]);
        }
    }
    public function updateBookReview(Request $request){
        DB::beginTransaction();
        try {
            $book = BookReview::find($request->id);
            
            if($book){
                if($book->user_id != auth()->user()->id){
                    return $this->getResponse403();
                }
                $book->comment = trim($request->comment);
                $book->edited = true;
                $book->user_id = trim(auth()->user()->id);
             //   $book->book_id = trim($request->book_id);
                $book->update();    
    
                DB::commit();
                return $this->getResponse200($book);
            }else{
                return $this->getResponse404();
            }
            
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500([$e->getMessage()]);
        }
    }
}
