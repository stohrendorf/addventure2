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
                        <span class="sr-only">{"toggle_plank"|i18n}</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="{$url.site}"><span class="glyphicon glyphicon-tree-deciduous" style="color:green;"></span>{'plank'|i18n}</a>
                </div>
                <div class="collapse navbar-collapse" id="navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li><a href="{$url.site}">{"trunk_of_tree"|i18n}</a></li>
                        <li{if ($url.current == 'recent')} class="active"{/if}><a href="{$url.site}/recent">{"freshest_leaves"|i18n}</a></li>
                        <li{if ($url.current == 'doc/random')} class="active"{/if}><a href="{$url.site}/doc/random">{"white_rabbit"|i18n}</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">{"tree_house"|i18n} <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="{$url.site}/treehouse/mostread"><span class="glyphicon glyphicon-eye-open"></span> {"most_read"|i18n}</a></li>
                                <li><a href="{$url.site}/treehouse/mostliked"><span class="glyphicon glyphicon-heart"></span> {"most_liked"|i18n}</a></li>
                                <li><a href="{$url.site}/treehouse/mosthated"><span class="glyphicon glyphicon-heart-empty"></span> {"most_hated"|i18n}</a></li>
                                <li><a href="{$url.site}/treehouse/mostepisodes"><span class="glyphicon glyphicon-pencil"></span> {"write_a_holics"|i18n}</a></li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        {if $client.userid!=-1}
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">{$client.username} <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="{$url.site}/account/logout"><span class="glyphicon glyphicon-off"></span> {"log_out"|i18n}</a></li>
                                    <li><a href="{$url.site}/account/changepassword"><span class="glyphicon glyphicon-cog"></span> {"change_password"|i18n}</a></li>
                                </ul>
                            </li>
                        {else}
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">{"log_in"|i18n} <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li>
                                        {login_form}
                                    </li>
                                </ul>
                            </li>
                        {/if}
                    </ul>
                </div>
            </div>
        </nav>
        <article class="content container page-body">
            {block name=body}{/block}
        </article>
    </body>
</html>