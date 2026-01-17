<?php
$getVerificationdata = getVerificationdata('tbl_verification', $leadDetails->lead_id);
$verification_status = array(1 => "PENDING", 2 => "POSITIVE", 3 => "NEGATIVE");
$relation_type = array(0 => "PENDING", 1 => "PARENTS", 2 => "RELATIVE", 3 => "FRIENDS", 4 => "COLLEAGUE", 5 => "OTHER");
$is_field_insvestigation = 'disabled';
if ($leadDetails->stage == "S5" || $leadDetails->stage == "S6" || $leadDetails->stage == "S11") {
    $is_field_insvestigation = '';
}
?>
<div id="verification_details"></div>

<style>
    .download-links { display: flex; }
    .download-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #0066cc;
        transition: color 0.3s ease;
        margin-right: 30px;
        margin-left: 30px;
    }

    .download-link:hover {
        color: #004499;
    }

    .icon {
        font-size: 35px;
    }
</style>
<div class="footer-support">
    <h2 class="footer-support">
        <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#BANKANALYSIS" onclick="getDataBankingAnalysis('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">BANKING ANALYSIS&nbsp;<i class="fa fa-angle-double-down"></i></button>
        <?php if(user_id == 37) { ?>
        <button type="button" class="btn btn-info collapse" data-toggle="collapse" data-target="#BANKANALYSIS" onclick="viewAnalysedBankingList('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">BANKING ANALYSIS&nbsp;<i class="fa fa-angle-double-down"></i></button>
        <?php } ?>
    </h2>
</div>

<div id="BANKANALYSIS" class="collapse">
    <div id="div_bank_statement_analysis">
        <?php if (((agent == 'CR2' && !empty($leadDetails->lead_credit_assign_user_id) && $leadDetails->lead_credit_assign_user_id == user_id) || agent == "CA") && in_array($leadDetails->lead_status_id, array(5, 6, 11)) && empty($leadDetails->lead_stp_flag)) { ?>
            <button class="btn btn-info" id="analyse_bank_statement" onclick="analyse_bank_statement('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Analyse Bank Statement</button>
            <?php if (user_id == 37) { ?>
            &nbsp;<div id="bsv_status_button">
                <input type="checkbox" class="checkbox_bsv" id="checkbox_bsv" name="checkbox_bsv" onclick="re_analyse_bank_statement('<?= $this->encrypt->encode($leadDetails->lead_id) ?>', 1)" autocomplete="off">
            </div>
            &nbsp;<div id="bsv_status_message">Click to analyse customer bank statement.</div>
            <?php } ?>
        <?php } ?>
    </div>
    <div id="viewAnalysedBankingList"></div>
    <div id="viewBankingAnalysisApiData"></div>
</div>
<div class="footer-support">
    <h2 class="footer-support">
            <button type="button" class="btn btn-info collapse" data-toggle="collapse" onclick="checkAACurrentStatus('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" data-target="#ACCOUNTAGRIGATOR">ACCOUNT AGGREGATOR &nbsp;<i class="fa fa-angle-double-down"></i></button>
            <!--
            <button type="button" class="btn btn-info collapse" data-toggle="collapse" onclick="get_aaStatus('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')" data-target="#ACCOUNTAGRIGATOR">ACCOUNT AGGREGATOR &nbsp;<i class="fa fa-angle-double-down"></i></button>
            -->
    </h2>
</div>
<div id="ACCOUNTAGRIGATOR" class="collapse">
    <div>
        <table class="table">
            <tbody>
            	<tr id="consentRequest" style="display:none">
                    <th></th>
                    <th>
                        <a class="btn btn-primary" id="AAconsentRequestBtn" href="javascript:void(0)" onclick="sendAAconsentRequest('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Send Consent Request for Bank Statement </a>

                        <a class="btn btn-primary" style="display:none" id="AAconsentRequestStatusBtn" href="javascript:void(0)" onclick="AAconsentRequestStatus('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Check Consent Request Status</a>

                        <a class="btn btn-primary" style="display:none" id="NPAAconsentRequestStatusBtn" href="javascript:void(0)" onclick="checkAACurrentStatus('<?= $this->encrypt->encode($leadDetails->lead_id) ?>')">Check Consent Request Status</a>

                        <button class="btn btn-primary btnLoading" style="display:none" type="button" disabled>
                            <span class="fa fa-spinner" role="status" aria-hidden="true"></span>
                            Loading...
                        </button>
                    </th>
                    <th></th>
                </tr>

                <!--
                <tr id="consentRequests" data-id="<?= $this->encrypt->encode($leadDetails->lead_id) ?>">
                    <th>
                        <span id="AAconsentMailBtn" style="display:none">
                            <a class="btn btn-primary" href="javascript:void(0)" onclick="send_aaConsentRequest('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Send Consent Request for Bank Statement </a>
                        </span>
                        <span id="AAconsentStatusBtn" style="display:none">
                            <a class="btn btn-primary" href="javascript:void(0)" onclick="get_aaStatus('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Check Consent Request Status</a>
                        </span>
                        <span id="AABankStatementBtn" style="display:none">
                            <a class="btn btn-primary" href="javascript:void(0)" onclick="get_aaStatus('<?= $this->encrypt->encode($leadDetails->lead_id) ?>','.btnLoading')">Get bank Statement </a>
                        </span>
                        <button class="btn btn-primary btnLoading" style="display:none" type="button" disabled>
                            <span class="fa fa-spinner" role="status" aria-hidden="true"></span>
                            Loading...
                        </button>
                    </th>
                    <th></th>
                </tr>
                -->
                <tr id="FI_request_form" style="display:none">
                    <td colspan="3">
                        <div class="row" style="padding: 20px;">
                            <div class="col-md-12 col-sm-12" style="padding-bottom: 0px;">
                                <h3>Financial Information request</h3>
                            </div>
                            <div class="col-md-2 col-sm-3">
                                <label>From Date</label>
                                <div class="form-group">
                                    <input readonly="" type="text" class="form-control" name="sfd" id="sfd" autocomplete="off" value="">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-3">
                                <label>From to</label>
                                <div class="form-group">
                                    <input readonly="" type="text" class="form-control" name="sed" id="sed" autocomplete="off" value="">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-3">
                                <label style="opacity:0">Button</label>
                                <div class="form-group">
                                    <a class="btn btn-primary" href="javascript:void(0)" onclick="AA_FIRequest('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Submit</a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr id="FI_request_status" style="display:none">
                    <td colspan="3">
                        <a class="btn btn-primary" href="javascript:void(0)" onclick="AA_FIRequest_status('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Get Financial Information Status</a>
                    </td>
                </tr>
                <tr id="bankAnalysisBox" style="display:none">
                    <td colspan="3">
                        <a class="btn btn-primary" href="javascript:void(0)" onclick="get_bankAnalysis('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Bank Statement Analysis</a>
                    </td>
                </tr>
                <tr id="bankStatementBox" style="display:none;background: #dfdffd;">
                    <td colspan="3">
                        <a class="btn btn-primary" href="javascript:void(0)" onclick="get_bankStatement('<?= $this->encrypt->encode($leadDetails->lead_id) ?>',this)">Get Bank Statement</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<script>
function get_aaStatus(lead_id)
{

    $.ajax
    ({
        url: "<?= base_url('account-aggregator/getConsentResponse/'); ?>" + lead_id,
        type: "GET",
        dataType : 'JSON',
        success: function(response)
        {
            if(response.aa_method_id == 5 && response.aa_provider == 2)
            {
                url = '<?= base_url('view-document-file/'); ?>' + response.doc_id+ "/1";
                //window.location.href = url;
            }
            else
            {
                $("#NPAAconsentRequestStatusBtn").attr('href',url);
            }
        }
    });

    //$(btn).css('display', 'none');
}
function checkAACurrentStatus(lead_id)
{
    $.ajax({
        url: "<?= base_url('account-aggregator/getAAconsentAllLog'); ?>/" + lead_id,
        type: "GET",
        dataType : 'JSON',
        success: function(response)
        {
            //console.log(response); return false;
            if (response.status == true)
            {
                var stepId = '';
                var stepIdText = '';
                var data = response.data[0];
                $("#AAconsentRequestBtn").css('display', 'none');
                if(data.aa_provider == 1 && data.aa_api_status_id == 1)
                {
                    if (data.aa_method_id == 1)
                    {
                        stepId = '#consentRequest';
                    }
                    if (data.aa_method_id == 2)
                    {
                        if(data.aa_status_message ==='REJECTED')
                        {
                            stepId = '#consentRequest';
                        }
                        else
                        {
                            stepId = '#FI_request_form';
                        }

                    }
                    if (data.aa_method_id == 3)
                    {
                        stepId = '#FI_request_status';
                    }
                    if (data.aa_method_id == 4)
                    {
                        stepId = '#bankStatementBox';
                    }
                    if ((data.aa_method_id == 5 || data.aa_method_id == 6))
                    {
                        stepId = '#bankStatementBox,#bankAnalysisBox';
                    }
                }
                else if(data.aa_provider == 2 && data.aa_api_status_id == 1)
                {
                    if(data.aa_method_id == 1)
                    {
                        stepId = '#consentRequest';
                        if(data.aa_status_message == '' || data.aa_status_message == null)
                        {
                            text = 'Consent Request is Pending from Customer Side';
                        }
                        else
                        {
                            text = data.aa_status_message;
                        }

                    }
                    if(data.aa_method_id == 2)
                    {
                        //In Progress
                        stepId = '#consentRequest';
                        text = 'Consent Request is Approved From Customer';
                    }
                    if(data.aa_method_id == 3)
                    {
                        //Rejected // Text Change
                        stepId = '#consentRequest';
                        text = 'Consent Request is Rejected from Customer Side';
                    }
                    if(data.aa_method_id == 4)
                    {
                        //Processed
                        stepId = '#consentRequest';
                        text = 'Bank Statement is Processed and Waiting for Download';
                    }
                    if(data.aa_method_id == 5)
                    {
                        //Ready to View
                        var stepId = '#consentRequest';
                        text = 'Get Bank Details Novel Pattern';
                    }
                }
                $(stepId).css('display', 'table-row');
                if (stepId == '#consentRequest')
                {
                    if(data.aa_provider == 1)
                    {
                        if(data.aa_method_id == 2 && data.aa_status_message ==='REJECTED')
                        {
                            $("#NPAAconsentRequestStatusBtn").css('display', 'block').text('Rejected By User');
                            $("#AAconsentRequestStatusBtn").css('display', 'none');
                        }
                        else
                        {
                            $("#NPAAconsentRequestStatusBtn").css('display', 'none');
                            $("#AAconsentRequestStatusBtn").css('display', 'block');
                        }
                    }

                    if(data.aa_provider == 2)
                    {
                        $("#AAconsentRequestStatusBtn").css('display', 'none');
                        $("#NPAAconsentRequestStatusBtn").css('display', 'block');
                        $("#NPAAconsentRequestStatusBtn").text(text);
                        if(data.aa_method_id == 5)
                        {
                            var url = '<?= base_url('view-document-file/'); ?>' + data.docs_id+ "/1";
                            $("#NPAAconsentRequestStatusBtn").attr('href',url);
                            $("#NPAAconsentRequestStatusBtn").attr('target','_blank');
                            $("#NPAAconsentRequestStatusBtn").attr('onclick','');
                        }
                    }
                }
            }
            else
            {
                $('#consentRequest').css('display', 'table-row');
            }
        }
    });
}
function sendAAconsentRequest(lead_id, btn)
{
    <?php if(AA_PROVIDER == 'NOVEL_PATTERN'):?>
    var url = "<?= base_url('account-aggregator/createConsentRequest/'); ?>";
    <?php elseif(AA_PROVIDER == 'SIGNZY'):?>
    var url = "<?= base_url('account-aggregator/consentRequest/'); ?>";
    <?php endif;?>
    $(btn).css('display', 'none');
    $(".btnLoading").css('display', 'inline-block');
    $.ajax({
        url:  url + lead_id,
        type: "GET",
        //data: csrf_token,
        success: function(response)
        {
            var res = JSON.parse(response);
            if (res.status == true)
            {
                <?php if(AA_PROVIDER == 'NOVEL_PATTERN'):?>
                $("#NPAAconsentRequestStatusBtn").css('display', 'inline-block');
                <?php elseif(AA_PROVIDER == 'SIGNZY'):?>
                $("#AAconsentRequestStatusBtn").css('display', 'inline-block');
                <?php endif;?>
                $("#AAconsentRequestBtn").css('display', 'none');
                $(".btnLoading").css('display', 'none');
                catchSuccess(res.message);
            } else {
                $(btn).css('display', 'inline-block');
                $("#AAconsentRequestStatusBtn").css('display', 'none');
                $(".btnLoading").css('display', 'none');
                catchError(res.message);
            }
        }
    });
}

function AAconsentRequestStatus(lead_id, btn)
{
    $(btn).css('display', 'none');
    $(".btnLoading").css('display', 'inline-block');
    $.ajax({
        url: "<?= base_url('account-aggregator/consentRequestStatus'); ?>/" + lead_id,
        type: "GET",
        //data: csrf_token,
        success: function(response) {
            console.log(response);
            var res = JSON.parse(response);
            if (res.status == true) {
                $("#FI_request_form").css('display', 'table-row');
                $("#consentRequest").css('display', 'none');
                $(".btnLoading").css('display', 'none');
                catchSuccess(res.message);
            } else {
                $(btn).css('display', 'inline-block');
                $("#AAconsentRequestStatusBtn").css('display', 'none');
                $(".btnLoading").css('display', 'none');
                catchError(res.message);
            }
        }
    });
}

function AA_FIRequest(lead_id, btn) {
    $(btn).html('<span class="fa fa-spinner" role="status" aria-hidden="true"></span>Loading...');
    $(btn).prop('disabled', true);
    var dateFrom = $('#sfd').val();
    var dateTo = $('#sed').val();
    $.ajax({
        url: "<?= base_url('account-aggregator/fiRequest'); ?>/" + lead_id,
        type: "GET",
        data: {
            dateFrom: dateFrom,
            dateTo: dateTo
        },
        success: function(response) {
            console.log(response);
            var res = JSON.parse(response);
            if (res.status == true) {
                $("#FI_request_form").css('display', 'none');
                $("#FI_request_status").css('display', 'block');
                catchSuccess(res.message);
            } else {
                $(btn).html('Submit');
                $(btn).prop('disabled', false);
                catchError(res.message);
            }
        }
    });
}
</script>
