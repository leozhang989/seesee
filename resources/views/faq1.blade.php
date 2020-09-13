<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>TestFlight无法接入APPStore解决方法</title>
	<style>
		body{
			word-break: break-word!important;
			word-break: break-all;
		}
		ol{
			display: block;
		    list-style-type: decimal;
		    margin-block-start: 1em;
		    margin-block-end: 1em;
		    margin-inline-start: 0px;
		    margin-inline-end: 0px;
		    padding-inline-start: 40px;
		}
		p{
			display: block;
		    margin-block-start: 1em;
		    margin-block-end: 1em;
		    margin-inline-start: 0px;
		    margin-inline-end: 0px;
		}
		.image-view{
			position: absolute;
		    top: 0;
		    left: 0;
		    width: 100%;
		    height: 100%;
		    overflow: hidden;
		}
		.image-packge{
			width: 100%;
		    margin: 0;
		    padding-bottom: 25px;
		    text-align: center;
		    font-size: 0;
		}
		.image-container{
			position: relative;
		    z-index: 95;
		    background-color: #e6e6e6;
		    transition: background-color .1s linear;
		    margin: 0 auto;
		}
		.image-container-fill{
			z-index: 90;
		}
		hr{
			margin: 0 0 20px;
		    border: 0;
		    border-top: 1px solid #eee!important;
		    -webkit-box-sizing: content-box;
		    box-sizing: content-box;
		    height: 0;
		    overflow: visible;
		}
		blockquote{
			padding: 20px;
		    background-color: #fafafa;
		    border-left: 6px solid #e6e6e6;
		    word-break: break-word;
		    font-size: 16px;
		    font-weight: normal;
		    line-height: 30px;
		    margin: 0 0 20px;
		}
		blockquote p{
			font-weight: 400;
		    line-height: 1.7;
		}
		.title{
			margin: 15px 0;
		    font-size: 30px;
		    font-weight: 700;
		    font-family: -apple-system,SF UI Display,Arial,PingFang SC,Hiragino Sans GB,Microsoft YaHei,WenQuanYi Micro Hei,sans-serif;
		    line-height: 1.4;
		    color: #2f2f2f;
		}
		h1{
			display: block;
		    font-size: 2em;
		    margin-block-start: 0.67em;
		    margin-block-end: 0.67em;
		    margin-inline-start: 0px;
		    margin-inline-end: 0px;
		    font-weight: bold;
		}
	</style>
</head>
<body style="display: block;margin: 3em;">
	<h1 class="title">TestFlight无法接入APPStore解决方法</h1>
	<div class="article" style="display: block;font-weight: 400;line-height: 1.8;margin-bottom: 20px;word-break: break-word;">
		<p>最近安装TestFlight时一直出现无法接入appstore connect问题，有些客户打开会提示不可用：</p>
		<br>
		<div class="image-package">
		<div class="image-container" style="max-width: 300px; max-height: 650px; background-color: transparent;">
		<div class="image-container-fill" style="padding-bottom: 216.67000000000002%;"></div>
		<div class="image-view" data-width="300" data-height="650"><img style="cursor: zoom-in;" class="" src="{{ asset('images/1.png') }}"></div>
		</div>
		</div>
		<p>其实是因为苹果官方对TF签名分发的地域进行了限制，有些地区用户是正常的，有些地区的用户就会收到上面的提示。</p>
		<h3>如何解决TestFlight 访问限制？</h3>
		<ol>
		<li>打开设置- 无线局域网 - 已连接的WIFI - 点击最右侧的“i”标识</li>
		</ol>
		<div class="image-package">
		<div class="image-container" style="max-width: 500px; max-height: 460px; background-color: transparent;">
		<div class="image-container-fill" style="padding-bottom: 92.0%;"></div>
		<div class="image-view" data-width="500" data-height="460"><img style="cursor: zoom-in;" class="" src="{{ asset('images/2.png') }}"></div>
		</div>
		</div>
		<ol start="2">
		<li>找到DNS，选择配置DNS</li>
		</ol>
		<div class="image-package">
		<div class="image-container" style="max-width: 500px; max-height: 468px; background-color: transparent;">
		<div class="image-container-fill" style="padding-bottom: 93.60000000000001%;"></div>
		<div class="image-view" data-width="500" data-height="468"><img style="cursor: zoom-in;" class="" src="{{ asset('images/3.png') }}"></div>
		</div>
		</div>
		<ol start="3">
		<li>选择手动， 删除原来的DNS，添加新服务器223.5.5.5</li>
		</ol>
		<div class="image-package">
		<div class="image-container" style="max-width: 500px; max-height: 627px; background-color: transparent;">
		<div class="image-container-fill" style="padding-bottom: 125.4%;"></div>
		<div class="image-view" data-width="500" data-height="627"><img style="cursor: zoom-in;" class="" src="{{ asset('images/4.png') }}"></div>
		</div>
		</div>
		<ol start="4">
		<li>点击存储后， 重新打开Safari浏览器，刷新下载链接即可。</li>
		</ol>
		<hr>
		<blockquote>
		<p><strong>关于DNS服务器，一个失效就换另外一个，多试验几次总有个能用的</strong><br>
		<strong>腾讯 DNS：</strong>119.29.29.29、182.254.116.116<br>
		<strong>阿里 DNS：</strong>223.5.5.5、223.6.6.6<br>
		<strong>百度 DNS：</strong>180.76.76.76</p>
		</blockquote>
	</div>
</body>
</html>
