<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Auth;
use App\User;
use App\Group;
use App\Post;
use App\Comment;
use App\Task;
use DB;
use Notifynder;
use Fenos\Notifynder\Models\Notification;

class AdminGroupController extends Controller
{
    public function showPanel($id){
    	if(Group::find($id)->hasUser(Auth::user()->id) ){
    		$group = Group::find($id);
    		return view('user.group.panel')->with([
    			'group' => $group
    		]);
    	}
    }

    public function edit($id){
    	if(Auth::user()->isLeaderGroup($id)){
    		$group = Group::find($id);
    		return view('user.group.edit')->with([
    			'group' => $group
    		]);
    	}
    }

	public function update(Request $request, $id){
		$group = Group::find($id);

		$group->name = $request->input('name');
		$group->description = $request->input('description');
		$group->github = $request->input('github');

		$group->save();
		return redirect('user/group/'.$id.'/panel');
	}

	public function member($id){
		$users = Group::find($id)->users()->get();
		$group = Group::find($id);
		return view('user.group.member')->with([
			'users' => $users,
			'group' => $group
		]);
	}

	public function deleteMember($id, $user_id){
		if(Auth::user()->isLeaderGroup($id)){
			Group::find($id)->removeUser(User::find($user_id));
			return redirect('user/group/'.$id.'/panel/member');
		}
	}

	public function changeLeader($id, $user_id){
		if(Auth::user()->isLeaderGroup($id)){
			$group = Group::find($id);
			$group->leader = $user_id;
			$group->save();
			return redirect('user/group/'.$id.'/panel/');
		}
	}

	public function searchMember($id){
		if(Auth::user()->isLeaderGroup($id)){
			$group = Group::find($id);
			$userInGroup = $group->users()->lists('id');
			$users = User::whereNotIn('id', $userInGroup)->get();
			return view('user.group.search-member', compact('users', 'group'));
		}
	}

	public function resultSearch(Request $request, $id){
		if(Auth::user()->isLeaderGroup($id)){
			$userInGroup = Group::find($id)->users()->lists('id');

			if($request->input('skill') == null && $request->input('hobby') == null){
				if($request->get('study') == 0){
					$users = DB::table('users')
							->select('users.*')
							->whereNotIn('users.id', $userInGroup)
							->get();	
					foreach($users as $user){
						if(User::find($user->id)->getSkills()) $user->skill = User::find($user->id)->getSkills();
						else $user->skill = "Chưa có";
						if(User::find($user->id)->getHobbies()) $user->hobby = User::find($user->id)->getHobbies();
						else $user->hobby = "Chưa có";
					}
					return response()->json($users);	
				}
				elseif($request->get('study') == 1){
					$users = DB::table('users')
							->leftJoin('studies', 'users.id', '=', 'studies.user_id')
							->select('users.*')
							->whereNotNull('studies.id')
							->whereNotIn('users.id', $userInGroup)
							->get();	
					foreach($users as $user){
						if(User::find($user->id)->getSkills()) $user->skill = User::find($user->id)->getSkills();
						else $user->skill = "Chưa có";
						if(User::find($user->id)->getHobbies()) $user->hobby = User::find($user->id)->getHobbies();
						else $user->hobby = "Chưa có";
					}
					return response()->json($users);
				}
				elseif($request->get('study') == 2){
					$users = DB::table('users')
							->leftJoin('studies', 'users.id', '=', 'studies.user_id')
							->select('users.*')
							->whereNull('studies.id')
							->whereNotIn('users.id', $userInGroup)
							->get();	
					foreach($users as $user){
						if(User::find($user->id)->getSkills()) $user->skill = User::find($user->id)->getSkills();
						else $user->skill = "Chưa có";
						if(User::find($user->id)->getHobbies()) $user->hobby = User::find($user->id)->getHobbies();
						else $user->hobby = "Chưa có";
					}
					return response()->json($users);
				}
				
			}
			elseif ($request->input('skill') != null && $request->input('hobby') == null) {
				if($request->get('study') == 0){
					$skill = $request->input('skill');
					$arr = explode(",", $skill);
					$users = DB::table('users')
							->join('skills', 'users.id', '=', 'skills.user_id')
							->select('users.*', 'skills.name as skill')
							->whereIn('skills.name', $arr, 'and')
							->whereNotIn('users.id', $userInGroup)
							->get();	
					foreach($users as $user){
						if(User::find($user->id)->getSkills()) $user->skill = User::find($user->id)->getSkills();
						else $user->skill = "Chưa có";
						if(User::find($user->id)->getHobbies()) $user->hobby = User::find($user->id)->getHobbies();
						else $user->hobby = "Chưa có";
					}
					return response()->json($users);
				}
				elseif($request->get('study') == 1){
					$skill = $request->input('skill');
					$arr = explode(",", $skill);
					$users = DB::table('users')
							->join('skills', 'users.id', '=', 'skills.user_id')
							->leftJoin('studies', 'users.id', '=', 'studies.user_id')
							->select('users.*', 'skills.name as skill')
							->whereIn('skills.name', $arr, 'and')
							->whereNotNull('studies.id')
							->whereNotIn('users.id', $userInGroup)
							->get();	
					foreach($users as $user){
						if(User::find($user->id)->getSkills()) $user->skill = User::find($user->id)->getSkills();
						else $user->skill = "Chưa có";
						if(User::find($user->id)->getHobbies()) $user->hobby = User::find($user->id)->getHobbies();
						else $user->hobby = "Chưa có";
					}
					return response()->json($users);
				}
				elseif($request->get('study') == 2){
					$skill = $request->input('skill');
					$arr = explode(",", $skill);
					$users = DB::table('users')
							->join('skills', 'users.id', '=', 'skills.user_id')
							->leftJoin('studies', 'users.id', '=', 'studies.user_id')
							->select('users.*', 'skills.name as skill')
							->whereIn('skills.name', $arr, 'and')
							->whereNull('studies.id')
							->whereNotIn('users.id', $userInGroup)
							->get();	
					foreach($users as $user){
						if(User::find($user->id)->getSkills()) $user->skill = User::find($user->id)->getSkills();
						else $user->skill = "Chưa có";
						if(User::find($user->id)->getHobbies()) $user->hobby = User::find($user->id)->getHobbies();
						else $user->hobby = "Chưa có";
					}
					return response()->json($users);
				}

			}
			elseif ($request->input('skill') == null && $request->input('hobby') != null) {
				if($request->get('study') == 0){
					$hobby = $request->input('hobby');
					$arr = explode(",", $hobby);
					$users = DB::table('users')
							->join('hobbies', 'users.id', '=', 'hobbies.user_id')
							->select('users.*', 'hobbies.name as hobby')
							->whereIn('hobbies.name', $arr)
							->whereNotIn('users.id', $userInGroup)
							->get();	
					foreach($users as $user){
						if(User::find($user->id)->getSkills()) $user->skill = User::find($user->id)->getSkills();
						else $user->skill = "Chưa có";
						if(User::find($user->id)->getHobbies()) $user->hobby = User::find($user->id)->getHobbies();
						else $user->hobby = "Chưa có";
					}
					return response()->json($users);
				}
				elseif($request->get('study') == 1){
					$hobby = $request->input('hobby');
					$arr = explode(",", $hobby);
					$users = DB::table('users')
							->join('hobbies', 'users.id', '=', 'hobbies.user_id')
							->leftJoin('studies', 'users.id', '=', 'studies.user_id')
							->select('users.*', 'hobbies.name as hobby')
							->whereIn('hobbies.name', $arr)
							->whereNotNull('studies.id')
							->whereNotIn('users.id', $userInGroup)
							->get();	
					foreach($users as $user){
						if(User::find($user->id)->getSkills()) $user->skill = User::find($user->id)->getSkills();
						else $user->skill = "Chưa có";
						if(User::find($user->id)->getHobbies()) $user->hobby = User::find($user->id)->getHobbies();
						else $user->hobby = "Chưa có";
					}
					return response()->json($users);
				}
				elseif($request->get('study') == 2){
					$hobby = $request->input('hobby');
					$arr = explode(",", $hobby);
					$users = DB::table('users')
							->join('hobbies', 'users.id', '=', 'hobbies.user_id')
							->leftJoin('studies', 'users.id', '=', 'studies.user_id')
							->select('users.*', 'hobbies.name as hobby')
							->whereIn('hobbies.name', $arr)
							->whereNull('studies.id')
							->whereNotIn('users.id', $userInGroup)
							->get();	
					foreach($users as $user){
						if(User::find($user->id)->getSkills()) $user->skill = User::find($user->id)->getSkills();
						else $user->skill = "Chưa có";
						if(User::find($user->id)->getHobbies()) $user->hobby = User::find($user->id)->getHobbies();
						else $user->hobby = "Chưa có";
					}
					return response()->json($users);
				}
			}
			else{
				if($request->get('study') == 0){
					$skill = $request->input('skill');
					$arr = explode(",", $skill);
					$hobby = $request->input('hobby');
					$arr2 = explode(",", $hobby);
					$users = DB::table('users')
							->join('skills', 'users.id', '=', 'skills.user_id')
							->join('hobbies', 'users.id', '=', 'hobbies.user_id')
							->select('users.*', 'skills.name as skill')
							->select('users.*', 'hobbies.name as hobby')
							->whereIn('skills.name', $arr, 'and')
							->whereIn('hobbies.name', $arr2)
							->whereNotIn('users.id', $userInGroup)
							->get();	
					foreach($users as $user){
						if(User::find($user->id)->getSkills()) $user->skill = User::find($user->id)->getSkills();
						else $user->skill = "Chưa có";
						if(User::find($user->id)->getHobbies()) $user->hobby = User::find($user->id)->getHobbies();
						else $user->hobby = "Chưa có";
					}
					return response()->json($users);
				}
				elseif($request->get('study') == 1){
					$skill = $request->input('skill');
					$arr = explode(",", $skill);
					$hobby = $request->input('hobby');
					$arr2 = explode(",", $hobby);
					$users = DB::table('users')
							->join('skills', 'users.id', '=', 'skills.user_id')
							->join('hobbies', 'users.id', '=', 'hobbies.user_id')
							->leftJoin('studies', 'users.id', '=', 'studies.user_id')
							->select('users.*', 'skills.name as skill')
							->select('users.*', 'hobbies.name as hobby')
							->whereIn('skills.name', $arr, 'and')
							->whereIn('hobbies.name', $arr2)
							->whereNotNull('studies.id')
							->whereNotIn('users.id', $userInGroup)
							->get();	
					foreach($users as $user){
						if(User::find($user->id)->getSkills()) $user->skill = User::find($user->id)->getSkills();
						else $user->skill = "Chưa có";
						if(User::find($user->id)->getHobbies()) $user->hobby = User::find($user->id)->getHobbies();
						else $user->hobby = "Chưa có";
					}
					return response()->json($users);
				}
				elseif($request->get('study') == 2){
					$skill = $request->input('skill');
					$arr = explode(",", $skill);
					$hobby = $request->input('hobby');
					$arr2 = explode(",", $hobby);
					$users = DB::table('users')
							->join('skills', 'users.id', '=', 'skills.user_id')
							->join('hobbies', 'users.id', '=', 'hobbies.user_id')
							->leftJoin('studies', 'users.id', '=', 'studies.user_id')
							->select('users.*', 'skills.name as skill')
							->select('users.*', 'hobbies.name as hobby')
							->whereIn('skills.name', $arr, 'and')
							->whereIn('hobbies.name', $arr2)
							->whereNull('studies.id')
							->whereNotIn('users.id', $userInGroup)
							->get();	
					foreach($users as $user){
						if(User::find($user->id)->getSkills()) $user->skill = User::find($user->id)->getSkills();
						else $user->skill = "Chưa có";
						if(User::find($user->id)->getHobbies()) $user->hobby = User::find($user->id)->getHobbies();
						else $user->hobby = "Chưa có";
					}
					return response()->json($users);
				}
			}
		}
	}

	public function requestToUser($id, $user_id){
		if(Auth::user()->isLeaderGroup($id)){
			$group_id = $id;
	        Notifynder::category('request.group.to.user')
	                    ->from(Group::find($id)->getLeader()->id)
	                    ->to($user_id)
	                    ->url('http://yoururl.com')
	                    ->extra(compact('group_id'))
	                    ->send();
	        return redirect('user/group/'.$group_id.'/panel/member/search');
	    }
    }

    public function acceptToUser($id, $user_id, $noti_id){
    	if(Auth::user()->isLeaderGroup($id)){
    		$group_id = $id;
    		$noti = Notification::find($noti_id);
    		$noti->read=0;
    		$noti->save();
	        Group::find($group_id)->addUser(User::find($user_id));
	        Notifynder::category('accept.group.to.user')
	                    ->from(Auth::user()->id)
	                    ->to(Group::find($group_id)->getLeader()->id)
	                    ->url('http://yoururl.com')
	                    ->extra(compact('group_id'))
	                    ->send();
	        return redirect('user/group/'.$group_id.'/panel/member');
	    }
    }

    public function getTimeline($id){
    	$group = Group::find($id);
    	$posts = Group::find($id)->posts()->get();
    	return view('user.group.timeline', compact('group', 'posts'));
    }
    
    protected function createPost($id, array $data)
    {
        return Post::create([
        	'subject' => $data['subject'],
            'content' => $data['content'],
            'user_id' => Auth::user()->id,
            'group_id' => $id,
        ]);
    }

    public function addPost($id, Request $request){
    	//if(Group::find($id)->hasUser(Auth::user()->id)){
    		$this->createPost($id, $request->all());
        	return response()->json();	
    	//}
    }

    protected function createComment($id, $post_id, array $data){
    	return Comment::create([
    		'content' => $data['content'],
    		'user_id' => Auth::user()->id,
    		'post_id' => $post_id,
     	]);
    }

    public function addComment($id, $post_id, Request $request){
    	$this->createComment($id, $post_id, $request->all());
    	return redirect('user/group/'.$id.'/panel/timeline');
    }

    public function manageTask($id){
    	$group = Group::find($id);
    	$users = $group->users()->get();
    	return view('user.group.task.index', compact('group', 'users'));
    }

    public function addTask($id, Request $request){
    	if(Auth::user()->isLeaderGroup($id)){
    		Task::create([
    			'name' => $request->input('name'),
    			'description' => $request->input('description'),
    			'status' => 'started',
    			'user_id' => $request->get('person'),
    			'group_id' => $id,
    		]);
    		return response()->json();
    	}
    }


    public function destroy( $id, $task_id,Request $request ) {
	    $task = Task::findOrFail( $task_id );

	    if ( $request->ajax() ) {
	        $task->delete( $request->all() );

	        return response(['msg' => 'Skill deleted', 'task_id' => $id, 'status' => 'success']);
	    }
	    return response(['msg' => 'Failed deleting the task', 'status' => 'failed']);
	}

    public function editTask($id, $task_id, Request $request){
    	if(Auth::user()->isLeaderGroup($id)){
    		$task = Task::find($task_id);
    		$task->name = $request->input('name');
    		$task->description = $request->input('description');
    		$task->user_id = $request->get('person');
    		$task->save();
    		return redirect('user/group/'.$id.'/panel/task/manage');
    	}
    }

    public function personalTask($id){
    	if(Group::find($id)->hasUser(Auth::user()->id)){
    		$group = Group::find($id);
    		$tasks = Auth::user()->tasks()->where('group_id', $group->id)->get();
    		return view('user.group.task.mytask', compact('group', 'tasks'));
    	}
    }
    public function changeStatusTask($id, $task_id){
    	if(Group::find($id)->hasUser(Auth::user()->id)){
    		if(Task::find($task_id)->status == "started"){
    			$task = Task::find($task_id);
    			$task->status = "finished";
    			$task->save();
    		}
    		elseif(Task::find($task_id)->status == "finished"){
    			$task = Task::find($task_id);
    			$task->status = "pending";
    			$task->save();
    		}
    		elseif(Task::find($task_id)->status == "pending"){
    			$task = Task::find($task_id);
    			$task->status = "finished";
    			$task->save();
    		}
    	}
    	return redirect('user/group/'.$id.'/panel/mytask');
    }

    public function adminChangeStatusTask($id, $task_id){
    	if(Group::find($id)->hasUser(Auth::user()->id)){
    		if(Task::find($task_id)->status == "started"){
    			$task = Task::find($task_id);
    			$task->status = "finished";
    			$task->save();
    		}
    		elseif(Task::find($task_id)->status == "finished"){
    			$task = Task::find($task_id);
    			$task->status = "pending";
    			$task->save();
    		}
    		elseif(Task::find($task_id)->status == "pending"){
    			$task = Task::find($task_id);
    			$task->status = "finished";
    			$task->save();
    		}
    	}
    	return redirect('user/group/'.$id.'/panel/task/manage');
    }

    public function deleteGroup($id, Request $request){
    	Group::destroy($id);
    	return redirect('/group');
    }
}
