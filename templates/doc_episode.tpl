{extends 'layout.tpl'}
{block name=title}
    Episode {$episode.id}
{/block}

{block name="headElements" append}
    {if isset($episode.author)}
        <link href="{$url.site}/rss.php?what=recent&count=100&user={$episode.author.user}" rel="alternate" type="application/rss+xml" title="The 100 most recent episodes written by {$episode.author.name|escape} (RSS 2.0)"/>
        <link href="{$url.site}/atom.php?what=recent&count=100&user={$episode.author.user}" rel="alternate" type="application/atom+xml" title="The 100 most recent episodes written by {$episode.author.name|escape} (ATOM)"/>
    {/if}
{/block}

{block name=body}
    {if !empty($episode.title)}
        <h3>{$episode.title}</h3>
    {else}
        <h3>Episode #{$episode.id}</h3>
    {/if}
    <div class="panel panel-primary">
        <div class="panel-body">
            #{$episode.id}
            {if isset($episode.author)}is written by <a href="{$url.site}/user/{$episode.author.user}">{$episode.author.name}</a>.{/if}
            {if isset($episode.created)}({$episode.created}){/if}
            <a href="{$url.site}/maintenance/illegal/{$episode.id}" style="color:red;" class="pull-right" id="report"
               data-toggle="tooltip" data-placement="right" title="This function is for reporting content that breaks the rules, not for crying about a bad story."> <span class="glyphicon glyphicon-fire"></span> Report inappropriate content</a>
        </div>
        <div class="panel-footer">
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
                       {$link.title|escape}
                    </a>
                </p>
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
                            <a href="{$url.site}/doc/{$link.fromEp}">
                                {$link.title|escape}
                            </a>
                        </li>
                    {/foreach}
                </ul>
            {/if}
            {if $episode.linkable}
                <p class="text-center text-info"><span class="glyphicon glyphicon-info-sign"></span> This episode is linkable.</p>
            {/if}
        </div>
        <script type="text/javascript">
            $('#links>p>a').each(function(i, e) {
                $(e).tooltip();
            });
            $('#report').tooltip();
        </script>
        {if isset($episode.parent)}
            <div class="panel-footer"><a href="{$url.site}/doc/{$episode.parent}"><span class="glyphicon glyphicon-circle-arrow-left"></span> Go to the parent episode.</a></div>
        {/if}
    </div>

{/block}
