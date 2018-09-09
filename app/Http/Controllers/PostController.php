<?php

namespace App\Http\Controllers;

use App\User;
use App\UserPost;
use App\UsersVote;
use App\Post;
use App\Image;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class PostController extends Controller
{
	/**
	 * Create a new post.
	 *
	 * @param \Illuminate\Http\Request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create(Request $request)
	{
		// Check request are made with file or base64
		if($request->hasFile('image_1') && $request->hasFile('image_1') && $request->has('title'))
		{
			// Get the files to storage and create entry
			$file_1 			= $request->file('image_1');
	    $extension_1 	= $file_1->getClientOriginalExtension();
	    $fileNameWithExt_1 = $file_1->getClientOriginalName();
	    $fileName_1		= pathinfo($fileNameWithExt_1, PATHINFO_FILENAME);
	    $fileName_1 	= time() . '_' . mt_rand() . '.' . $extension_1;
	    $folderpath  	= base_path().'/storage/app/images/users/';
	    $file_1->move($folderpath, $fileName_1);
			$this->compress($folderpath.$fileName_1, $folderpath.$fileName_1, 75);

	    $image_1 = Image::create([
						    	'path' => $folderpath.$fileName_1,
						    	]);

	    $file_2 			= $request->file('image_2');
	    $extension_2 	= $file_2->getClientOriginalExtension();
	    $fileNameWithExt_2 = $file_2->getClientOriginalName();
	    $fileName_2 	= pathinfo($fileNameWithExt_2, PATHINFO_FILENAME);
	    $fileName_2 	= time() . '_' . mt_rand() . '.' . $extension_2;
	    $folderpath  	= base_path().'/storage/app/images/users/';
	    $file_2->move($folderpath, $fileName_2);
			$this->compress($folderpath.$fileName_2, $folderpath.$fileName_2, 75);

	    $image_2 = Image::create([
						    	'path' => $folderpath.$fileName_2,
						    	]);


	    if( $image_1->id == null || $image_2->id == null ) {
				unlink($image_1->path);
				unlink($image_2->path);

	    	$image_1->delete();
	    	$image_2->delete();
	    	return response()->json(['message' => 'Unable to create post. Image storage errors.'], 404);
	    }

	    // Create post entry of the 2 images
	    $post = Post::create([
	    	'title' 			 => $request->title,
	    		'image_1_id' => $image_1->id,
	    		'image_2_id' => $image_2->id
	    	]);

	    // Crete user relationship to the post
	    $user = $request['req']->getUser();
	    $userPost = UserPost::create([
	    	'user_id' => $user->id,
	    	'post_id' => $post->id
	    	]);

			return response()->json(['message' => "Post submitted successful."], 200);
		}
		else if($request->has('image_1') && $request->has('image_1') && $request->has('title')) {

			$binary_1 	= $request->image_1;
			$extension_1 = 'png';
			if( strpos($binary_1, 'data:image/jpeg;base64') !== false ) {
				$extension_1 = 'jpeg';
			}

			$binary_1 		= preg_replace('/^data:image\/[a-z]+;base64,/', '', $binary_1);
			$tempImage_1 	= base64_decode(str_replace(' ', '+', $binary_1));
	    
	    $imageName_1	= time() . '_' . mt_rand() . '.' .$extension_1;
			$imagePath_1 	= base_path().'/storage/app/images/users/'.$imageName_1;
			file_put_contents($imagePath_1, $tempImage_1);
			$this->compress($imagePath_1, $imagePath_1, 75);

	    $image_1 = Image::create([
						    	'path' => $imagePath_1,
						    	]);


	    $binary_2 		= $request->image_2;
			$extension_2 	= 'png';
			if( strpos($binary_2, 'data:image/jpeg;base64') !== false ) {
				$extension_2 = 'jpeg';
			}

			$binary_2 		= preg_replace('/^data:image\/[a-z]+;base64,/', '', $binary_2);
			$tempImage_2 	= base64_decode(str_replace(' ', '+', $binary_2));
	    
	    $imageName_2	= time() . '_' . mt_rand() . '.' . $extension_2;
			$imagePath_2 	= base_path().'/storage/app/images/users/'.$imageName_2;
			file_put_contents($imagePath_2, $tempImage_2);
			$this->compress($imagePath_2, $imagePath_2, 75);

	    $image_2 = Image::create([
						    	'path' => $imagePath_2,
						    	]);


	    if( $image_1->id == null || $image_2->id == null ) {
				unlink($image_1->path);
				unlink($image_2->path);

	    	$image_1->delete();
	    	$image_2->delete();
	    	return response()->json(['message' => 'Unable to create post. Image storage errors.'], 404);
	    }

	    // Create post entry of the 2 images
	    $post = Post::create([
	    		'title' 			=> $request->title,
	    		'image_1_id' 	=> $image_1->id,
	    		'image_2_id' 	=> $image_2->id
	    	]);

	    // Crete user relationship to the post

	    $user = $request['req']->getUser();
	    $userPost = UserPost::create([
	    	'user_id' => $user->id,
	    	'post_id' => $post->id
	    	]);

			return response()->json(['message' => "Post submitted successful."], 200);
		} else {
	    	return response()->json(['message' => 'Unable to create post. Incorrect request'], 404);
		}
	}

	/**
	 * Create a new post.
	 *
	 * @param \Illuminate\Http\Request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function get(Request $request)
	{
		$offset = 0;
		if($request->has('offset'))
			$offset = $request->offset;

		$posts = Post::orderBy('created_at', 'desc')->skip($offset)->take(10)->select('id', 'title', 'image_1_id', 'image_2_id', 'image_1_votes', 'image_2_votes')->get();
		
		$outOfPosts = false;
		if( count($posts) < 10 ) {
			$outOfPosts = true;
		}

	  return response()->json([
	  	'message' 	=> 'Posts fetch succesfully',
	  	'result'	 	=> [
	  			'posts' => $posts,
	  			'end' 	=> $outOfPosts
	  		]
	  	], 200);
	}

	/**
	 * Vote a post
	 *
	 * @param \Illuminate\Http\Request, integer, integer
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function vote(Request $request, $postId, $newVote)
	{
    $user = $request['req']->getUser();

    // Check $postId is an existing post
		$post = Post::find($postId);
		if( !$post ) {
    	return response()->json(['message' => 'Unable to find post. Incorrect request'], 404);
		}

		// Check if user already voted on the post, if not make a new entry, else edit the old entry
		$userVote = UsersVote::where('user_id', $user->id)->where('post_id', $postId)->first();
		if( !$userVote ) {

			// Create entry of user voting ont the post
    	$userVote = UsersVote::create([
    			'user_id' 	=> $user->id,
    			'post_id' 	=> $postId,
    			'vote' 			=> $newVote
    		]);

    	// Increment the vote on the post
    	if( $newVote == 1 )
    		$post->image_1_votes = $post->image_1_votes + 1; 
    	else if( $newVote == 2 )
    		$post->image_2_votes = $post->image_2_votes + 1;
  		$post->save();

		} else {

			// Get the user old vote
			// If the old vote is the same as the new one, do nothing
			// Else edit the vote 
			$oldVote = $userVote->vote;
			if( $oldVote != $newVote ) {

				// Edit user vote entry of the post
				$userVote = UsersVote::where('user_id', $user->id)->where('post_id', $postId)->first();
				$userVote->vote = $newVote;
				$userVote->save();

				// Decrement the vote from the old vote
				if( $oldVote == 1 )
	    		$post->image_1_votes = $post->image_1_votes - 1;
	    	else if( $oldVote == 2 )
	    		$post->image_2_votes = $post->image_2_votes - 1;
	  		
	  		// Increment the new vote
				if( $newVote == 1 )
	    		$post->image_1_votes = $post->image_1_votes + 1;
	    	else if( $newVote == 2 )
	    		$post->image_2_votes = $post->image_2_votes + 1;

	    	// Save the voted post
	  		$post->save();
			}
		}

	  return response()->json([
	  	'message' 	=> 'Post voted succesfully'
	  	], 200);
	}

	/**
	 * Compress an image
	 *
	 * @param string, string, integer
	 *
	 * @return string
	 */
	protected function compress($source, $destination, $quality) {

    $info = getimagesize($source);

    if ($info['mime'] == 'image/jpeg') 
        $image = imagecreatefromjpeg($source);

    elseif ($info['mime'] == 'image/png') 
        $image = imagecreatefrompng($source);

    imagejpeg($image, $destination, $quality);

    return $destination;
	}
}