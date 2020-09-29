<div class="panel">
    <h2>{l s='Discount coupons created' mod='SendEmailCoupon'}</h2>
    <div class="moduleconfig-content">
        <div class="row">
            <div class="col-md-1">
                <span>{l s='Id' mod='SendEmailCoupon'}</span>
            </div>
            <div class="col-md-3">
                <span>{l s='Customer' mod='SendEmailCoupon'}</span>
            </div>
            <div class="col-md-3">
                <span>{l s='Email' mod='SendEmailCoupon'}</span>
            </div>
            <div class="col-md-3">
                <span>{l s='Code' mod='SendEmailCoupon'}</span>
            </div>
            <div class="col-md-1">
                <span>{l s='Date' mod='SendEmailCoupon'}</span>
            </div>
        </div>
        {foreach $coupons as $coupon}
            <div class="row">
                <div class="col-md-1">
                    <span>{$coupon['id_customer']|escape:'htmlall':'UTF-8'}</span>
                </div>
                <div class="col-md-3">
                    <span>{$coupon['firstname']|escape:'htmlall':'UTF-8'} {$coupon['lastname']|escape:'htmlall':'UTF-8'}</span>
                </div>
                <div class="col-md-3">
                    <span>{$coupon['email']|escape:'htmlall':'UTF-8'}</span>
                </div>
                <div class="col-md-3">
                    <span>{$coupon['code']|escape:'htmlall':'UTF-8'}</span>
                </div>
                <div class="col-md-1">
                    <span>{$coupon['date_from']|escape:'htmlall':'UTF-8'}</span>
                </div>
            </div>
        {/foreach}
    </div>
</div>