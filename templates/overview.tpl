<h2>Overview</h2>
<hr>

 <div class="row">
    <div class="col-sm-5">
        Monthly Bandwidth limit
    </div>
    <div class="col-sm-7">
        {$MonthlyBandwidthLimit}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        Bandwidth used
    </div>
    <div class="col-sm-7">
        {$MonthlyBandwidthUsed}
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        Origin URL
    </div>
    <div class="col-sm-7">
        {$OriginUrl}
    </div>
</div>
    
    <div class="row">
        <div class="col-sm-5">
            CDN URL
        </div>
        <div class="col-sm-7">
            {foreach from=$Hostnames item=hostname}
                {if $hostname->IsSystemHostname eq 1}
                    {$hostname->Value}
                {/if}
            {/foreach}
        </div>
    </div>

    <div class="row">
        <div class="col-sm-5">
            Hostnames
        </div>
        <div class="col-sm-7">
            {foreach from=$Hostnames item=hostname}
                {if $hostname->IsSystemHostname neq 1}
                    {$hostname->Value}
                {/if}
            {/foreach}
        </div>
    </div>


<h3>{$LANG.clientareaproductdetails}</h3>

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

{foreach from=$configurableoptions item=configoption}
    <div class="row">
        <div class="col-sm-5">
            {$configoption.optionname}
        </div>
        <div class="col-sm-7">
            {if $configoption.optiontype eq 3}
                {if $configoption.selectedqty}
                    {$LANG.yes}
                {else}
                    {$LANG.no}
                {/if}
            {elseif $configoption.optiontype eq 4}
                {$configoption.selectedqty} x {$configoption.selectedoption}
            {else}
                {$configoption.selectedoption}
            {/if}
        </div>
    </div>
{/foreach}

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
    {if $packagesupgrade}
        <div class="col-sm-4">
            <a href="upgrade.php?type=package&amp;id={$id}" class="btn btn-success btn-block">
                {$LANG.upgrade}
            </a>
        </div>
    {/if}

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
