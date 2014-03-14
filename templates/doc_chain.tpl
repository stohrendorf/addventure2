{extends 'layout.tpl'}
{block name=title}
    The chain up to Episode {$targetEpisode}
{/block}

{block name=body}
    {foreach $episodes as $episode}
        <div class="panel panel-default">
            <div class="panel-heading">
                {if isset($episode.chosen)}<span class="glyphicon glyphicon-share-alt"></span> <em>{$episode.chosen}</em>{/if}
                {if isset($episode.title)}<h4>{$episode.title}</h4>{/if}
                {if isset($episode.author)} by <a href="{$url.site}/user/{$episode.author.user}">{$episode.author.name}</a>.{/if}
                {if isset($episode.created)}({$episode.created}){/if}
                <a href="{$url.site}/illegal/{$episode.id}" style="color:red;" class="pull-right" id="report"
                   data-toggle="tooltip" data-placement="right" title="This function is for reporting content that breaks the rules, not for crying about a bad story."> <span class="glyphicon glyphicon-fire"></span> Report inappropriate content</a>
                   <div class="clearfix"></div>
            </div>
            <div class="panel-footer">
                <span class="glyphicon glyphicon-eye-open"></span> {$episode.hitcount}
                <span class="glyphicon glyphicon-heart"></span> {$episode.likes}
                <span class="glyphicon glyphicon-heart-empty"></span> {$episode.dislikes}
                <div class="pull-right">
                    What do <em>you</em> think?
                    <a href="{$url.site}/like/{$episode.id}"> <span class="glyphicon glyphicon-heart"></span> I like it!</a>
                    <a href="{$url.site}/dislike/{$episode.id}"> <span class="glyphicon glyphicon-heart-empty"></span> Na, could have been better...</a>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>

        {if isset($episode.preNotes)}
            <div class="panel panel-primary">
                <div class="panel-heading">Author's Notes</div>
                <div class="panel-body">{$episode.preNotes}</div>
                <div class="panel-footer"><small>This note has been <em>automatically</em> taken from the episode's legacy title, as it seemed pretty long.
                        But machines aren't perfect: do you think this is wrong? <a href="{$url.site}/maintenance/reportTitle/{$episode.id}">Report it!</a></small></div>
            </div>
        {/if}

        <div class="panel panel-default"><div class="panel-body" style="background-color:#fffcee;">{$episode.text}</div></div>

        {if isset($episode.notes)}
            <div class="panel panel-primary">
                <div class="panel-heading">Author's Notes</div>
                <div class="panel-body">{$episode.notes}</div>
                <div class="panel-footer"><small>This note has been <em>automatically</em> extracted from the legacy episode's author name.
                        But machines aren't perfect: do you think this was done wrong? <a href="{$url.site}/maintenance/reportNotes/{$episode.id}">Report it!</a></small></div>
            </div>
        {/if}
    {/foreach}
{/block}
