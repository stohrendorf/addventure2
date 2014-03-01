<!DOCTYPE html>
<html>
    <head>
        <title>{block name=title}{/block}</title>
        <meta charset="UTF-8"/>
        <script src="{$url.jquery}"></script>
        <script src="{$url.bootstrap.js}"></script>
        <link href="{$url.bootstrap.css}" rel="stylesheet"/>
        <link href="{$url.bootstrap.theme}" rel="stylesheet"/>
        <link href="{$url.site}/rss.php?what=recent&count=100" rel="alternate" type="application/rss+xml" title="The 100 most recent episodes (RSS 2.0)"/>
        <link href="{$url.site}/atom.php?what=recent&count=100" rel="alternate" type="application/rss+xml" title="The 100 most recent episodes (ATOM)"/>
        {literal}<style>
                a:visited{color:purple;}
                a.unwritten-episode{color:#AA0000;}
                body > * {max-width:960px;}
            </style>{/literal}
        {block name=headElements}{/block}
    </head>
    <body style="margin:auto;">
        <header class="page-header center-block">
            <h1>Addventure2 <small>your forest of imagination...</small></h1>
        </header>
        <nav class="navbar navbar-default center-block">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
                        <span class="sr-only">Toggle the plank</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="{$url.site}"><span class="glyphicon glyphicon-tree-deciduous"></span>Plank</a>
                </div>
                <div class="collapse navbar-collapse" id="navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li><a href="./">Trunk of the Tree</a></li>
                        <li{if isset($smarty.get.recent)} class="active"{/if}><a href="{$url.site}/recent">The Freshest Leaves</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Login <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <form class="navbar-form form-signin" method="POST" action="{$url.site}/login">
                                        <h5 class="form-signin-heading">Log in or <a href="{$url.site}/register">register</a>.</h5>
                                        <input class="form-control" type="email" placeholder="E-Mail" name="email" required autofocus/>
                                        <input class="form-control" type="password" placeholder="Password" name="password" required/>
                                        <label class="checkbox">
                                            <input type="checkbox" value="rememberme"/> Remember me
                                        </label>                               
                                        <button class="btn btn-default btn-block" type="submit">Login!</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <article class="content container page-body">
        {block name=body}{/block}
    </article>
</body>
</html>