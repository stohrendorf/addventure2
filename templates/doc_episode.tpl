{extends 'layout.tpl'}
{block name=title}
    &raquo;{$episode.autoTitle}&laquo;{if isset($episode.author)} {t}by{/t} {$episode.author.name}{/if}
{/block}

{block name="headElements" append}
    {if isset($episode.author)}
        <link href="{$url.site}/feed/rss/{$episode.author.user}" rel="alternate" type="application/rss+xml"
              title="{t escape=no 1={$episode.author.name|escape}}Recent episodes by &raquo;%1&laquo; (RSS 2.0){/t}"/>
        <link href="{$url.site}/feed/atom/{$episode.author.user}" rel="alternate" type="application/atom+xml"
              title="{t escape=no 1={$episode.author.name|escape}}Recent episodes by &raquo;%1&laquo; (ATOM){/t}"/>
    {/if}
{/block}

{block name=body}
    {function name=childTree depth=2}
        {foreach $tree as $child}
            <div style="margin:0 0 0.3em {1.3*$depth}em; font-size: {(100-6*$depth)}%;">
                <a href="{$url.site}/doc/{$child.id}">{$child.title}</a>
                {if isset($child.children)}
                    {call name=childTree tree=$child.children depth={$depth+1}}
                {/if}
            </div>
        {/foreach}
    {/function}
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 style="display:inline;" class="panel-title">
                &raquo;{$episode.autoTitle}&laquo;
                {if isset($episode.author) or isset($episode.created)}
                    <span class="text-info">
                        {if isset($episode.author)} {t}by{/t} <a href="{$url.site}/user/{$episode.author.user}">{$episode.author.name}</a>{/if}
                        {if isset($episode.created)} @ {$episode.created}{/if}
                    </span>
                {/if}
            </h3>
            <a href="{$url.site}/maintenance/illegal/{$episode.id}" style="color:red;" class="pull-right" id="report"
               data-toggle="tooltip" data-placement="right" title="{t}This function is for reporting content that breaks the rules, not for crying about a bad story.{/t}"> <span class="glyphicon glyphicon-fire"></span> {t}Report inappropriate content{/t}</a>
            <span class="clearfix"></span>
            {if !empty($episode.tags)}
                <span class="glyphicon glyphicon-tags"></span>
                {foreach $episode.tags as $tag}
                    <a href="docs/bytag/{$tag.id}">{$tag.title}</a>
                {/foreach}
            {/if}
        </div>
        <div class="panel-body">
            {t 1={$episode.hitcount} 2={$episode.likes} 3={$episode.dislikes}}It has been seen %1 times, and %2 people liked it, while %3 didn't.{/t}
            <div class="pull-right">
                {t escape=no}What do <em>you</em> think?{/t}
                <a href="{$url.site}/like/{$episode.id}"> <span class="glyphicon glyphicon-heart"></span> {t}I like it!{/t}</a>
                <a href="{$url.site}/dislike/{$episode.id}"> <span class="glyphicon glyphicon-heart-empty"></span> {t}Na, could have been better...{/t}</a>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>

    {call name="showEpisode"}
    <div class="panel panel-primary">
        <div class="panel-body" id="links">
            {$canSubscribe=false}
            {foreach $episode.children as $link}
                {if !empty($link.subtree)}
                    <a id="show-descendants" class="pull-right" style="cursor:pointer;" data-toggle="tooltip" data-placement="right" title="{t}Show episode tree. (May contain spoilers!){/t}">
                        <div style="top:0;right:0;" id="show-descendants-plus"><span class="glyphicon glyphicon-plus-sign" style="font-size:2em;"></span></div>
                        <div style="top:0;right:0;display:none;" id="show-descendants-minus"><span class="glyphicon glyphicon-minus-sign" style="font-size:2em;"></span></div>
                    </a>
                    <script type="text/javascript">
                        $(function() {
                            $('#show-descendants').tooltip();
                            $('#show-descendants').click(function() {
                                $('div.episode-descendants').each(function(i, e) {
                                    $(e).collapse('toggle');
                                    if(i==0) {
                                        $(e).on('shown.bs.collapse', function() {
                                            $('html, body').animate({
                                                scrollTop: $('#links-top').offset().top
                                            }, 'slow');
                                        });
                                    }
                                });
                                var plus = $('#show-descendants-plus');
                                var minus = $('#show-descendants-minus');
                                if(plus.css('display') === 'none') {
                                    minus.fadeOut('slow', function(){ plus.fadeIn('slow'); });
                                }
                                else {
                                    plus.fadeOut('slow', function(){ minus.fadeIn('slow'); });
                                }
                            });
                        });
                    </script>
                    <span id="links-top"></span>
                    {break}
                {/if}
            {/foreach}
            {foreach $episode.children as $link}
                <p style="margin:0 0 0.3em 1em;">
                    <a href="{$url.site}/doc/{$link.toEp}"
                       {if !$link.isWritten}
                           class="unwritten-episode" data-toggle="tooltip" data-placement="left" title="{t}If you feel inspired now, you can add a new leaf to the tree.{/t}">
                           <span class="glyphicon glyphicon-pencil"></span>
                           {$canSubscribe=true}
                       {elseif !$link.isBacklink}
                           ><span class="glyphicon glyphicon-arrow-right"></span>
                       {else}
                           data-toggle="tooltip" data-placement="left" title="{t}This will take you to a (possibly) distant relative.{/t}">
                           <span class="glyphicon glyphicon-random"></span>
                       {/if}
                       {$link.title}
                    </a>
                </p>
                {if !empty($link.subtree)}
                    <div class="episode-descendants collapse">
                        {call name=childTree tree=$link.subtree}
                    </div>
                {/if}
            {/foreach}
            {if $canSubscribe && $client.canSubscribe}
                <p>
                    <a type="button" href="{$url.site}/doc/subscribe/{$episode.id}" class="btn btn-block btn-default btn-sm">
                        <span class="glyphicon glyphicon-envelope"></span>
                        {t}Notify me when new options are filled.{/t}
                    </a>
                </p>
            {/if}
            {if !empty($episode.backlinks)}
                <h5>{t}Backlinks to this Episode{/t}</h5>
                <ul>
                    {foreach $episode.backlinks as $link}
                        <li>
                            <a href="{$url.site}/doc/{$link.fromEp}">{$link.title}</a>
                        </li>
                    {/foreach}
                </ul>
            {/if}
            {if $episode.linkable}
                <p class="text-center text-info"><span class="glyphicon glyphicon-info-sign"></span> {t}This episode is linkable.{/t}</p>
            {/if}
        </div>
        <script type="text/javascript">
            $(function() {
                $('#links>p>a').each(function(i, e) {
                    $(e).tooltip();
                });
                $('#report').tooltip();
            });
        </script>
        {if isset($episode.parent)}
            <div class="panel-footer"><a href="{$url.site}/doc/{$episode.parent}"><span class="glyphicon glyphicon-circle-arrow-left"></span> {t}Go to the parent episode.{/t}</a></div>
        {/if}
    </div>

{/block}
