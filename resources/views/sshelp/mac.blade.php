<!DOCTYPE html>
<html lang="en-US"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Begin Jekyll SEO tag v2.6.1 -->
    <title>苹果 macOS 使用 Shadowsocks 设置教程 | Shadowsocks</title>
    <script type="application/ld+json">
{"headline":"苹果 macOS 使用 Shadowsocks 设置教程","@type":"WebPage","url":"https://xunjie.in/sshelp/mac","description":"Shadowsocks 终极使用指南","@context":"https://schema.org"}</script>
    <!-- End Jekyll SEO tag -->

    <style class="anchorjs"></style><link rel="stylesheet" href="{{ asset('sshelp/css/mac_style.css') }}">
</head>
<body>
<div class="container-lg px-3 my-5 markdown-body">

    <h1><a href="http://xunjie.in/sshelp/Shadowsocks">Shadowsocks</a></h1>


    <h1 id="苹果-macos-使用-shadowsocks-设置教程">苹果 macOS 使用 Shadowsocks 设置教程</h1>

    <h2 id="第一步-安装-shadowsocksx-ng">第一步 安装 ShadowsocksX-NG</h2>

    <p><strong>在安装之前，请始终确保您的系统满足最低系统要求。</strong></p>

    <p>您需要具备 MacOS 10.11 或更高版本才能运行 ShadowsocksX-NG。如果您的操作系统版本较旧， 则请先升级到 MacOS 10.11 或更高版本。按照下面的说明在 MacOS 上下载并安装 ShadowsocksX-NG。</p>

    <h4 id="1-下载客户端">1. 下载客户端</h4>

    <table>
        <tbody>
        <tr>
            <td><a href="https://github.com/shadowsocks/ShadowsocksX-NG/releases/download/v1.8.2/ShadowsocksX-NG.app.1.8.2.zip">ShadowsocksX-NG 下载</a></td>
            <td><a href="https://sourceforge.net/projects/shadowsocksgui/files/dist/ShadowsocksX-2.6.3.dmg/download">Sourceforge 备用下载</a></td>
            <td><a href="https://github.com/shadowsocks/ShadowsocksX-NG/releases/">历史版本</a></td>
        </tr>
        </tbody>
    </table>

    <h4 id="2-安装客户端">2. 安装客户端</h4>

    <p>双击解压 <code class="language-plaintext highlighter-rouge">ShadowsocksX-NG.x.x.x.zip</code> , 获取 <code class="language-plaintext highlighter-rouge">ShadowsocksX-NG</code>。</p>

    <p><img src="{{ asset('sshelp/images/mac1.png') }}" alt="安装客户端"></p>

    <p>将 “ShadowsocksX-NG” 拖移到 “访达”里面的 “应用程序”。</p>

    <p><img src="{{ asset('sshelp/images/mac2.gif') }}" alt="&quot;ShadowsocksX-NG&quot; 移动到 &quot;访达&quot; 里面的 &quot;应用程序&quot;"></p>

    <p>在 “应用程序” 中双击 “ShadowsocksX-NG” &gt; 选择 “打开”。</p>

    <p><img src="{{ asset('sshelp/images/mac3.png') }}" alt="选择打开"></p>

    <h2 id="第二步-获取-shadowsocks-账号信息">第二步 获取 Shadowsocks 账号信息</h2>

    <p>获取 Shadowsocks 账号信息</p>

    <h2 id="第三步-配置-shadowsocks-账号">第三步 配置 Shadowsocks 账号</h2>

    <h4 id="在您的电脑上-执行下列操作">在您的电脑上， 执行下列操作：</h4>

    <ul>
        <li>点击屏幕顶部菜单栏的 <img src="{{ asset('sshelp/images/mac5.png') }}" alt="menu_icon_disabled"> &gt; “服务器” &gt; “服务器设置”。</li>
    </ul>

    <p><img src="{{ asset('sshelp/images/mac6.png') }}" alt="点击屏幕最上方菜单栏"></p>

    <ul>
        <li>点击窗口上的 “+” &gt; 填写 “地址” &gt; 填写 “服务端口” &gt; 选择 ”加密方法”。</li>
        <li>填写 “密码“ &gt; 填写”备注” 为可选项。</li>
        <li>点击 “打开Shadowsocks” 。</li>
        <li>当显示 <code class="language-plaintext highlighter-rouge">Shadowsocks: On</code>时，表示系统代理已经打开。</li>
    </ul>

    <p><img src="{{ asset('sshelp/images/mac7.png') }}" alt="服务器设置，打开ss"></p>

    <h4 id="可以通过二维码方式单独增加节点-在您的电脑上-执行下列操作">可以通过二维码方式单独增加节点， 在您的电脑上， 执行下列操作：</h4>

    <p>此二维码同样适用于其他客户端。</p>

    <ul>
        <li>点击屏幕顶部菜单栏的 <img src="{{ asset('sshelp/images/mac5.png') }}" alt="menu_icon_disabled"> &gt; “扫描屏幕上的二维码” &gt; 当看到 “已添加新的Shadowsocks服务器”，代表添加成功。</li>
        <li>点击 “打开Shadowsocks” 。</li>
        <li>当显示 <code class="language-plaintext highlighter-rouge">Shadowsocks: On</code>时，表示系统代理已经打开。</li>
    </ul>

    <p><img src="{{ asset('sshelp/images/mac8.png') }}" alt="打开ss"></p>

    <h2 id="配置系统代理模式">配置系统代理模式</h2>
    <ul>
        <li>点击屏幕右上方菜单栏的 <img src="{{ asset('sshelp/images/mac5.png') }}" alt="menu_icon_disabled">  &gt; “PAC自动模式”。</li>
    </ul>

    <p><img src="{{ asset('sshelp/images/mac9.png') }}" alt="pac设置"></p>

    <h2 id="其他">其他</h2>
    <ul>
        <li><strong>PAC 模式</strong> 表示可以实现自动代理， 及本来可以访问的网站不会经过代理，推荐日常使用。</li>
        <li><strong>全局模式</strong> 表示计算机内大多数流量都会经过代理， 不推荐日常使用。</li>
    </ul>

    <h3 id="返回首页"><a href="http://xunjie.in/sshelp/Shadowsocks">«&nbsp;返回首页</a></h3>
</div>
</body>
</html>
