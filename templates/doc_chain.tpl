{extends 'layout.tpl'}
{block name=title}
    The chain up to Episode {$targetEpisode}
{/block}

{block name=body}
    {foreach $episodes as $episode}
        <div class="panel panel-default">
            <div class="panel-heading">
                {if isset($episode.chosen)}<span class="glyphicon glyphicon-share-alt"></span> <em>{$episode.chosen}</em>{/if}
                <h4><a data-toggle="tooltip" data-placement="left" title="Explore from here." href="{$url.site}/doc/{$episode.id}">
                {if !empty($episode.title)}
                    {$episode.title}
                {else}
                    Episode #{$episode.id}
                {/if}
                </a>
                {if isset($episode.author) or isset($episode.created)}
                    <span class="small pull-right">
                    {if isset($episode.author)}by <a href="{$url.site}/user/{$episode.author.user}">{$episode.author.name}</a>{/if}
                    {if isset($episode.created)}({$episode.created}){/if}
                    </span>
                    <span class="clearfix"></span>
                {/if}
                </h4>
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

        {call name="showEpisode"}
    {/foreach}
    <script type="text/javascript">
        $('a[title!=\'\']').each(function(i, e) {
            $(e).tooltip();
        });
    </script>
{/block}
