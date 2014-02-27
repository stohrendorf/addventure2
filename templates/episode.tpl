{extends 'layout.tpl'}
{block name=title}
    Episode {$episode.id}
{/block}

{block name="headElements" append}
    {if isset($episode.author)}
        <link href="./rss.php?what=recent&count=100&user={$episode.author.user}" rel="alternate" type="application/rss+xml" title="The 100 most recent episodes written by {$episode.author.name|escape} (RSS 2.0)"/>
        <link href="./atom.php?what=recent&count=100&user={$episode.author.user}" rel="alternate" type="application/atom+xml" title="The 100 most recent episodes written by {$episode.author.name|escape} (ATOM)"/>
    {/if}
{/block}

{block name=body}
    {if isset($episode.title)}<h3>{$episode.title}</h3>{/if}
    <div class="panel panel-primary">
        <div class="panel-body">
            #{$episode.id}
            {if isset($episode.author)}is written by <a href="?user={$episode.author.user}">{$episode.author.name}</a>.{/if}
            {if isset($episode.created)}({$episode.created}){/if}
            <a href="?illegal={$episode.id}" style="color:red;" class="pull-right" id="report"
               data-toggle="tooltip" data-placement="right" title="This function is for reporting content that breaks the rules, not for crying about a bad story."> <span class="glyphicon glyphicon-fire"></span> Report inappropriate content</a>
        </div>
        <div class="panel-footer">
            It has been seen {$episode.hitcount} times, and {$episode.likes} people liked it, while {$episode.dislikes} didn't.
            <div class="pull-right">
                What do <em>you</em> think?
                <a href="?like={$episode.id}"> <span class="glyphicon glyphicon-heart"></span> I like it!</a>
                <a href="?dislike={$episode.id}"> <span class="glyphicon glyphicon-heart-empty"></span> Na, could have been better...</a>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>

    {if isset($episode.preNotes)}
        <div class="panel panel-primary">
            <div class="panel-heading">Author's Notes</div>
            <div class="panel-body">{$episode.preNotes}</div>
            <div class="panel-footer"><small>This note has been <em>automatically</em> taken from the episode's legacy title, as it seemed pretty long.
                    But machines aren't perfect: do you think this is wrong? <a href="?maintenance=reportTitle&docid={$episode.id}">Report it!</a></small></div>
        </div>
    {/if}

    <div class="panel panel-default"><div class="panel-body" style="background-color:#fffcee;">{$episode.text}</div></div>

    {if isset($episode.notes)}
        <div class="panel panel-primary">
            <div class="panel-heading">Author's Notes</div>
            <div class="panel-body">{$episode.notes}</div>
            <div class="panel-footer"><small>This note has been <em>automatically</em> extracted from the legacy episode's author name.
                    But machines aren't perfect: do you think this was done wrong? <a href="?maintenance=reportNotes&docid={$episode.id}">Report it!</a></small></div>
        </div>
    {/if}
    <div class="panel panel-primary">
        <div class="panel-body" id="links">
            {if count($episode.children)>0}
                {foreach $episode.children as $link}
                    <p style="margin:0 0 0.3em 1em;">
                        <a href="?doc={$link.toEp}"
                           {if !$link.isWritten}
                               class="unwritten-episode" data-toggle="tooltip" data-placement="left" title="If you feel inspired now, you can add a new leaf to the tree.">
                               <span class="glyphicon glyphicon-pencil"></span>
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
            {/if}
            {if count($episode.backlinks)>0}
                <h5>Backlinks to this Episode</h5>
                <ul>
                    {foreach $episode.backlinks as $link}
                        <li>
                            <a href="?doc={$link.fromEp}">
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
        <script type="text/javascript">$('#links>p>a').each(function(i, e) {
                $(e).tooltip();
            });
            $('#report').tooltip();</script>
            {if isset($episode.parent)}
            <div class="panel-footer"><a href="?doc={$episode.parent}"><span class="glyphicon glyphicon-circle-arrow-left"></span> Go to the parent episode.</a></div>
        {/if}
    </div>

{/block}
