<?php

namespace App\Http\Controllers;

use App\Models\followers;
use App\Models\Images;
use App\Models\likes;
use Carbon\Traits\ToStringFormat;
use Illuminate\Http\Request;
use App\Models\Products;
use DateTime;
use GuzzleHttp\Pool;
use PhpParser\Node\Expr\Cast\String_;
use Validator;

use function PHPUnit\Framework\isEmpty;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
  
    public function index()
    {
        $query=Products::query()->where('isshow',true);
        $user=auth()->user()->id;
        $product=$query->with(['image','user'])->paginate(10);

        $like=likes::where('user_id',$user)->pluck('product_id')->toArray();
        foreach ($product as $pro ) {
            $pro->liked=in_array($pro->id,$like);
        }
        $follower=followers::where('follower_id',$user)->pluck('followed_id')->toArray();
        foreach ($product as $pro ) {
            $pro->isfollowed=in_array($pro->user->id,$follower);
        }


        return response()->json($product, 200);
    }

  
    /**
     * search for product 
     * 
     */
    public function search(Request $request)
    {
        $query=Products::query()->where('isshow',true);
        $user=auth()->user()->id;
        $value=$request->input('value');
        if($request->filled('value')){
            $query->where(function ($q) use($value){
                $q->where('name','LIKE',"%{$value}%")->orWhere('group','LIKE',"%{$value}%");
            });
        }
        if($value){
            $query->orWhereHas('user',function ($q) use($value){
                $q->where('store_name','LIKE',"%{$value}%");});

        }

        //$product=$query->paginate(2);
        $product=$query->with(['image','user'])->paginate(10);

        $like=likes::where('user_id',$user)->pluck('product_id')->toArray();
        foreach ($product as $pro ) {
            $pro->liked=in_array($pro->id,$like);
        }



        return response()->json($product,
        200);
    


        
    }

    /**
     * get user products 
     */
    public function getUserProduct()
    {
        $query=Products::query();
        $user=auth()->user()->id;
        $query->where('user_id','LIKE',auth()->user()->id);
           
        //$product=$query->paginate(2);
        $product=$query->with(['image','user'])->paginate(1000);

        $like=likes::where('user_id',$user)->pluck('product_id')->toArray();
        foreach ($product as $pro ) {
            $pro->liked=in_array($pro->id,$like);
        }


    
        return response()->json($product,200);
        
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user=auth()->user();
        $request['user_id']=$user->id;
        $ss=$this->validate($request,[
            'user_id'=>'required',
            'group'=>'required', 
            'name'=>'required', 
            'currency'=>'required', 
            'price'=>'required', 
            'image'=>'required', 
            'description'=>'required', 
         
         ]);

        $data=Products::Create($ss);
        // save image in database and file
        
        $images=$request->file('image');
        if($request->hasFile('image')){
            foreach ($images as $key => $image) {
            //$image=$request->file('image');
            
                $new_image=rand().'.'.$image->getClientOriginalExtension();
                $image->move(public_path('product-image'),$new_image);
                $dataimage=Images::Create([ 'product_id'=>$data->id,'image'=>$new_image]);
                
            } 
        }
        /*
        $image=$request->file('image');
        if($request->hasFile('image')){
            $new_image=rand().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('product-image'),$new_image);
            $dataimage=Images::Create([ 'product_id'=>$data->id,'image'=>$new_image]);
            
        }*/
        return response()->json(["message"=>"تم الاضافةاعلانك تحت المراجعة","dataImage"=>count($request->image)],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $query=Products::query();
        $user=auth()->user()->id;
        $product=$query->with(['group','image','user'])->find($id);

        $like=likes::where('user_id',$user)->pluck('product_id')->toArray();
            $product['liked']=in_array($product->id,$like);
        


        return response()->json(["data"=>$product],
        200);
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
        $input=Products::find($id);   
        if($input->user_id===auth()->user()->id){
        
        $data=$this->validate($request,[
            'group'=>'required', 
            'name'=>'required', 
            'currency'=>'required', 
            'price'=>'required', 
            //'country'=>'required', 
            'description'=>'required',
         
         ]);

         $input->update($data);
         return response()->json(['success'=>true,
            'message'=>'تم تحدبث المعلومات بنجاح',
            'data'=>$input
            ],200);

        }else{
             return response()->json(['success'=>false,
            'message'=>'خطأ',
            'data'=>[]
            ],400);

        }
        
      
         
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product=Products::find($id);
        if($product->user_id==auth()->user()->id){
        
        $product->delete();
        return response()->json([
            'success'=>true,
            'message'=>'تم الحذف بنجاح',
            'data'=>$product
        ]);
    }
    }
}
