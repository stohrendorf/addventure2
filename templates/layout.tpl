{include 'utils.tpl'}<!DOCTYPE html>
<html>
    <head>
        <title>{block name=title}{/block}</title>
        <meta charset="UTF-8"/>
        <script src="{$url.jquery}"></script>
        <script src="{$url.bootstrap.js}"></script>
        <link href="{$url.bootstrap.css}" rel="stylesheet"/>
        <link href="{$url.bootstrap.theme}" rel="stylesheet"/>
        <link href="{$url.site}/feed/rss" rel="alternate" type="application/rss+xml" title="Recent episodes (RSS 2.0)"/>
        <link href="{$url.site}/feed/atom" rel="alternate" type="application/atom+xml" title="Recent episodes (ATOM)"/>
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
                        <span class="sr-only">{t}Toggle the plank{/t}</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="{$url.site}"><span class="glyphicon glyphicon-tree-deciduous" style="color:green;"></span>{t}Plank{/t}</a>
                </div>
                <div class="collapse navbar-collapse" id="navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li><a href="{$url.site}">{t}Trunk of the Tree{/t}</a></li>
                        <li{if ($url.current == 'recent')} class="active"{/if}><a href="{$url.site}/recent">{t}The Freshest Leaves{/t}</a></li>
                        <li{if ($url.current == 'doc/random')} class="active"{/if}><a href="{$url.site}/doc/random">{t}The White Rabbit{/t}</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">{t}The Tree House{/t} <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="{$url.site}/treehouse/mostread"><span class="glyphicon glyphicon-eye-open"></span> {t}Most read episodes{/t}</a></li>
                                <li><a href="{$url.site}/treehouse/mostliked"><span class="glyphicon glyphicon-heart"></span> {t}Most liked episodes{/t}</a></li>
                                <li><a href="{$url.site}/treehouse/mosthated"><span class="glyphicon glyphicon-heart-empty"></span> {t}Most hated episodes{/t}</a></li>
                                <li><a href="{$url.site}/treehouse/mostepisodes"><span class="glyphicon glyphicon-pencil"></span> {t}Write-a-holics{/t}</a></li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            {if $client.userid!=-1}
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">{$client.username} <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="{$url.site}/account/logout"><span class="glyphicon glyphicon-off"></span> {t}Log out{/t}</a></li>
                                    <li><a href="{$url.site}/account/changepassword"><span class="glyphicon glyphicon-cog"></span> {t}Change password{/t}</a></li>
                                </ul>
                            {else}
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">{t}Login{/t} <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <form class="navbar-form form-signin" action="{$url.site}/account/login" method="POST">
                                            {csrf_field}
                                            <h5 class="form-signin-heading">
                                                {t escape=no}Log in or <a href="{$url.site}/account/register">register</a>.{/t}
                                            </h5>
                                            <input class="form-control" type="text" placeholder="Username" name="username" required autofocus/>
                                            <input class="form-control" type="password" placeholder="Password" name="password" required/>
                                            <div class="checkbox">
                                                <label>
                                                    <input class="form-control" type="checkbox" name="remember" id="remember" value="yes"/>
                                                    {t}Remember me{/t}
                                                </label>
                                            </div>
                                            <button class="btn btn-outline btn-block" type="submit">{t}Login!{/t}</button>
                                            <a href="{$url.site}/account/recover">{t}Forgot your password?{/t}</a>
                                        </form>
                                    </li>
                                </ul>
                            {/if}
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