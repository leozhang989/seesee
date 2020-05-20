<!DOCTYPE html>
<html lang="en-US"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Begin Jekyll SEO tag v2.6.1 -->
    <title>微软 Windows 使用 Shadowsocks 设置教程 | Shadowsocks</title>
    <script type="application/ld+json">
{"headline":"微软 Windows 使用 Shadowsocks 设置教程","@type":"WebPage","url":"https://xunjie.in/sshelp/windows","description":"Shadowsocks 终极使用指南","@context":"https://schema.org"}</script>
    <!-- End Jekyll SEO tag -->

    <style class="anchorjs"></style><link rel="stylesheet" href="{{ asset('sshelp/css/windows_style.css') }}">
</head>
<body>
<div class="container-lg px-3 my-5 markdown-body">

    <h1><a href="http://xunjie.in/sshelp">Shadowsocks</a></h1>


    <h1 id="微软-windows-使用-shadowsocks-设置教程">微软 Windows 使用 Shadowsocks 设置教程</h1>

    <h2 id="第一步-下载">第一步 下载</h2>

    <p>下载任意一个软件压缩包，下载后解压至任意目录安装。</p>

    <table>
        <tbody>
        <tr>1、下载：</tr>
        <tr>
            <td><a href="https://github.com/shadowsocks/shadowsocks-windows/releases/download/4.1.7.1/Shadowsocks-4.1.7.1.zip">最新版 Shadowsocks-4.1.7.1</a></td>
            <td><a href="https://github.com/shadowsocks/shadowsocks-windows/releases/download/4.0.10/Shadowsocks-4.0.10.zip">推荐 Shadowsocks-4.0.10</a></td>
            <td><a href="https://github.com/shadowsocks/shadowsocks-windows/releases/download/3.2/Shadowsocks-3.2.zip">XP系统 Shadowsocks-3.2</a></td>
            <td><a href="https://github.com/shadowsocks/shadowsocks-windows/releases">历史版本</a></td>
        </tr>
        </tbody>
    </table>

    <p>2、安装过程中 若提示.NET framework过低，则需要下载.NET framework软件<a href="https://www.microsoft.com/zh-CN/download/details.aspx?id=53344">点击下载</a>，重新打开运行即可。</p>

    <p>注：需要安装 <a href="https://dotnet.microsoft.com/download/dotnet-framework/net472">.NET Framework 4.6.2 </a>和 Microsoft <a href="https://www.microsoft.com/en-us/download/details.aspx?id=53840">Visual C++ 2015 Redistributable (x86)</a>（一般已默认安装不需再次安装）。</p>

    <h2 id="第二步-使用教程">第二步 使用教程</h2>

    <p>1、下载后解压文件，打开EXE文件安装后，右键单击左下角任务栏的Shadowsocks【小飞机图标】进行配置。</p>

    <p>2、在 【服务器】 菜单添加服务器节点。</p>

    <p>3、选择 【启用系统代理】来启用系统代理。注：请禁用浏览器里的代理插件，或把它们设置为使用系统代理。</p>

    <p>4、然后可以打开 www.google.com 进行测试。注：若游览器无法打开google.com等网页，可能是你的游览器有插件或者设置了代理，可以尝试更换游览器测试一下。</p>

    <p><img src="{{ asset('sshelp/images/windows1.png') }}" alt=""></p>

    <h2 id="最后-其他设置说明">最后 其他设置说明</h2>

    <h3 id="一服务器节点添加说明目前主要有三种配置节点信息的方法可以根据你的习惯和需要选择">一、服务器节点添加说明，目前主要有三种配置节点信息的方法，可以根据你的习惯和需要选择</h3>

    <p>方法一、从剪切板导入URL【推荐】每次复制SS链接，点击从剪切板导入URL即可配置服务器</p>

    <p>1、首先复制SS地址二维码链接</p>

    <p>2、然后右键单击右下角的软件，点击“服务器”－“从剪切板导入URL”</p>

    <p>3、程序自动识别SS地址并导入服务器节点信息，最后启用系统代理即可使用</p>

    <p><img src="{{ asset('sshelp/images/windows2.png') }}" alt=""></p>

    <p>方法二、扫二维码配置【推荐】	通过扫描屏幕上的二维码，自动配置，推荐</p>

    <p>1、首先网页上或者是聊天窗口打开节点的二维码图片</p>

    <p>2、然后右键单击右下角的软件，点击“服务器”－“扫描屏幕上的二维码”</p>

    <p>3、程序自动识别二维码并导入服务器节点信息，最后启用系统代理即可使用</p>

    <p><img src="{{ asset('sshelp/images/windows3.png') }}" alt=""></p>

    <p>方法三、手动编辑服务器配置	添加服务器，并逐一配置相关节点信息</p>

    <p>1、右键单击右下角的软件，点击“服务器”－“编辑服务器”</p>

    <p>2、逐一输入节点服务器【地址（域名或者IP地址）、端口、密码】，选择加密方式后确定</p>

    <p>3、保存服务器节点信息，最后启用系统代理即可使用</p>

    <h3 id="二系统代理模式---全局模式pac模式">二、系统代理模式 - 全局模式/PAC模式</h3>

    <p>1、全局模式：你可能会遇到一些网站打不开，仍然无法访问，这个你可以试试选择【系统代理模式-全局模式】，这样使全部流量经过节点服务器。</p>

    <p>2、PAC模式【推荐】：选择PAC模式，PAC文件网址列表走节点服务器，国内网址则走你自己使用的网络流量。</p>

    <p>3、关于PAC更新，你可以直接从 <a href="https://github.com/gfwlist/gfwlist">GFWList</a> （由第三方维护）更新 PAC 文件，或者你可以手动编辑本地pac文件。需要更新PAC：依次操作：PAC -&gt;从GFW List更新PAC （等待更新完毕后）-&gt;使用本地PAC-&gt;启动系统代理。</p>

    <p><img src="{{ asset('sshelp/images/windows4.jpg') }}" alt=""></p>


    <h3 id="返回首页"><a href="http://xunjie.in/sshelp">«&nbsp;返回首页</a></h3>
</div>
</body>
</html>
