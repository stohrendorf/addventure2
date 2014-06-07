{extends 'layout.tpl'}
{block name=title}
    &raquo;{$episode.autoTitle}&laquo; by {$episode.author.name}
{/block}

{block name="headElements" append}
    {if isset($episode.author)}
        <link href="{$url.base}/rss.php?what=recent&amp;count=100&amp;user={$episode.author.user}" rel="alternate" type="application/rss+xml" title="The 100 most recent episodes by {$episode.author.name|escape} (RSS 2.0)"/>
        <link href="{$url.base}/atom.php?what=recent&amp;count=100&amp;user={$episode.author.user}" rel="alternate" type="application/atom+xml" title="The 100 most recent episodes by {$episode.author.name|escape} (ATOM)"/>
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
                        {if isset($episode.author)} by <a href="{$url.site}/user/{$episode.author.user}">{$episode.author.name}</a>{/if}
                        {if isset($episode.created)} @ {$episode.created}{/if}
                    </span>
                {/if}
            </h3>
            <a href="{$url.site}/maintenance/illegal/{$episode.id}" style="color:red;" class="pull-right" id="report"
               data-toggle="tooltip" data-placement="right" title="This function is for reporting content that breaks the rules, not for crying about a bad story."> <span class="glyphicon glyphicon-fire"></span> Report inappropriate content</a>
            <span class="clearfix"></span>
        </div>
        <div class="panel-body">
            It has been seen {$episode.hitcount} times, and {$episode.likes} people liked it, while {$episode.dislikes} didn't.
            <div class="pull-right">
                What do <em>you</em> think?
                <a href="{$url.site}/like/{$episode.id}"> <span class="glyphicon glyphicon-heart"></span> I like it!</a>
                <a href="{$url.site}/dislike/{$episode.id}"> <span class="glyphicon glyphicon-heart-empty"></span> Na, could have been better...</a>
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
                    <a id="show-descendants" class="pull-right" style="cursor:pointer;" data-toggle="tooltip" data-placement="right" title="Show episode tree. (May contain spoilers!)">
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
                                                scrollTop: $('#links-bottom').offset().top
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
                    {break}
                {/if}
            {/foreach}
            {foreach $episode.children as $link}
                <p style="margin:0 0 0.3em 1em;">
                    <a href="{$url.site}/doc/{$link.toEp}"
                       {if !$link.isWritten}
                           class="unwritten-episode" data-toggle="tooltip" data-placement="left" title="If you feel inspired now, you can add a new leaf to the tree.">
                           <span class="glyphicon glyphicon-pencil"></span>
                           {$canSubscribe=true}
                       {elseif !$link.isBacklink}
                           ><span class="glyphicon glyphicon-arrow-right"></span>
                       {else}
                           data-toggle="tooltip" data-placement="left" title="This will take you to a (possibly) distant relative.">
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
                        Notify me when new options are filled.
                    </a>
                </p>
            {/if}
            {if !empty($episode.backlinks)}
                <h5>Backlinks to this Episode</h5>
                <ul>
                    {foreach $episode.backlinks as $link}
                        <li>
                            <a href="{$url.site}/doc/{$link.fromEp}">{$link.title}</a>
                        </li>
                    {/foreach}
                </ul>
            {/if}
            {if $episode.linkable}
                <p class="text-center text-info"><span class="glyphicon glyphicon-info-sign"></span> This episode is linkable.</p>
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
            <div class="panel-footer"><a href="{$url.site}/doc/{$episode.parent}"><span class="glyphicon glyphicon-circle-arrow-left"></span> Go to the parent episode.</a></div>
        {/if}
        <span id="links-bottom"></span>
    </div>

{/block}
