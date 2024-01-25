<div style="color: #fff;">
    <h2>Overview</h2>

    <hr>
    <div class="row">
        <div class="col-sm-5">
            {$LANG.clientareahostingregdate}
        </div>
        <div class="col-sm-7">
            {$regdate}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-5">
            {$LANG.orderproduct}
        </div>
        <div class="col-sm-7">
            {$groupname} - {$product}
        </div>
    </div>


    {if $domain}
        <div class="row">
            <div class="col-sm-5">
                {$LANG.orderdomain}
            </div>
        </div>
    {/if}



    {foreach from=$productcustomfields item=customfield}
        <div class="row">
            <div class="col-sm-5">
                {$customfield.name}
            </div>
            <div class="col-sm-7">
                {$customfield.value}
            </div>
        </div>
    {/foreach}

    {if $lastupdate}
        <div class="row">
            <div class="col-sm-5">
                {$LANG.clientareadiskusage}
            </div>
            <div class="col-sm-7">
                {$diskusage}MB / {$disklimit}MB ({$diskpercent})
            </div>
        </div>
        <div class="row">
            <div class="col-sm-5">
                {$LANG.clientareabwusage}
            </div>
            <div class="col-sm-7">
                {$bwusage}MB / {$bwlimit}MB ({$bwpercent})
            </div>
        </div>
    {/if}

    <div class="row">
        <div class="col-sm-5">
            {$LANG.orderpaymentmethod}
        </div>
        <div class="col-sm-7">
            {$paymentmethod}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-5">
            {$LANG.firstpaymentamount}
        </div>
        <div class="col-sm-7">
            {$firstpaymentamount}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-5">
            {$LANG.recurringamount}
        </div>
        <div class="col-sm-7">
            {$recurringamount}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-5">
            {$LANG.clientareahostingnextduedate}
        </div>
        <div class="col-sm-7">
            {$nextduedate}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-5">
            {$LANG.orderbillingcycle}
        </div>
        <div class="col-sm-7">
            {$billingcycle}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-5">
            {$LANG.clientareastatus}
        </div>
        <div class="col-sm-7">
            {$status}
        </div>
    </div>

    {if $suspendreason}
        <div class="row">
            <div class="col-sm-5">
                {$LANG.suspendreason}
            </div>
            <div class="col-sm-7">
                {$suspendreason}
            </div>
        </div>
    {/if}

    <hr>

    <div class="row">
        <div class="col-sm-4">
            <form method="post" action="clientarea.php?action=productdetails">
                <input type="hidden" name="id" value="{$serviceid}" />
                <input type="hidden" name="customAction" value="manage" />
                <a href="{$view}" target="_blank" class="btn btn-default btn-block">
                    View In Dashboard
                </a>
            </form>
        </div>

        <div class="col-sm-4">
            <a href="clientarea.php?action=cancel&amp;id={$id}" class="btn btn-danger btn-block{if $pendingcancellation}disabled{/if}">
                {if $pendingcancellation}
                    {$LANG.cancellationrequested}
                {else}
                    {$LANG.cancel}
                {/if}
            </a>
        </div>
    </div>
</div>