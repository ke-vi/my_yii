<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta name="viewport"
	content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>登陆丨Kevi</title>
<link rel="stylesheet" href="/loginweb/style.css">
</head>
<body>

	<div class="login-container">
		<h1>kevi</h1>

		<div class="connect">
			<p style="left: 0%;">欢迎来到 kevi 个人网站</p>
		</div>

		<form novalidate="novalidate" action="/admin/admin/login" method="post" id="loginForm">
			<div>
				<input name="username" class="username" placeholder="用户名"
					autocomplete="off" type="text">
			</div>
			<div>
				<input name="password" class="password" placeholder="密码"
					oncontextmenu="return false" onpaste="return false" type="password">
			</div>
			<button id="submit" type="submit">登 陆</button>
		</form>

		<a href="/admin/admin/toregister">
			<button type="button" class="register-tis">还有没有账号？</button>
		</a>

	</div>


	<script src="/loginweb/jquery_002.js"></script>
	<script src="/loginweb/common.js"></script>
	<!--背景图片自动更换-->
	<script src="/loginweb/supersized.js"></script>
	<script src="/loginweb/supersized-init.js"></script>
	<!--表单验证-->
	<script src="/loginweb/jquery.js"></script>
	<ul style="visibility: visible;" class="speed" id="supersized">
		<li style="visibility: visible; opacity: 0.3787;"
			class="slide-0 activeslide"><a target="_blank"><img
				style="width: 1287px; left: 0px; top: -112px; height: 810.81px;"
				src="/loginweb/1.jpg"></a></li>
		<li style="visibility: visible; opacity: 1;" class="slide-1"><a
			target="_blank"><img
				style="width: 1287px; left: 0px; top: -163.5px; height: 810.81px;"
				src="/loginweb/2.jpg"></a></li>
		<li style="visibility: visible; opacity: 1;" class="slide-2 prevslide"><a
			target="_blank"><img
				style="width: 1287px; height: 810.81px; left: 0px; top: -118.5px;"
				src="/loginweb/3.jpg"></a></li>
		<li style="visibility: visible; opacity: 1;" class="slide-2 prevslide"><a
			target="_blank"><img
				style="width: 1287px; height: 810.81px; left: 0px; top: -118.5px;"
				src="/loginweb/4.jpg"></a></li>
	</ul>
</body>
</html>