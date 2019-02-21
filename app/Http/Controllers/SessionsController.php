<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class SessionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest',[
            'only' => ['create']
        ]);
    }

	/**
	 * 登录页面
	 * @return [type] [description]
	 */
    public function create()
    {
    	return view('sessions.create');
    }

    /**
     * 登录
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function store(Request $request)
    {
    	//credentials 证书 凭据 国书
    	$credentials  = $this->validate($request,[
    		'email' => 'required|email|max:255',
    		'password' => 'required'
    	]);

    	//attempt 企图 视图 攻击  flash 反射、闪光 flush 激动 面红 萌芽 奔流 
    	if( Auth::attempt( $credentials,$request->has('remember') ) ){
            if( Auth::user()->activated ){
                session()->flash('success','欢迎回来！');
                //intended 登录成功后进入用户之前访问的页面
                return redirect()->intended(route('users.show',[Auth::user()]));
            }else{
                Auth::logout();
                session()->flash('warning','你的账号未激活，请检查邮箱中的注册邮件进行激活。');
                return redirect('/');
            }
    	}else{
    		session()->flash('danger','很抱歉，您的邮箱和密码不匹配');
    		
    		// 失败后，应该返回上一页面，保留上次输入，但不应该携带密码
    		// return redirect()->back(); //不生效
    		return back()->withInput();
    	}
    }

    public function destroy()
    {
        Auth::logout();
        session()->flash('success','您已成功退出');
        return redirect('login');
    }

}
