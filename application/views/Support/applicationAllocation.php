<section class="parent_wrapper">
    <?php
    $this->load->view('Layouts/header');
    include('inner_layout.php');
    ?>
    <div class="container-fluid">
        <div class="taskPageSize taskPageSizeDashboard">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-container list-menu-view">
                        <div class="page-content">
                            <div class="main-container">
                                <div class="container-fluid">
                                    <?php if (agent == 'CA') { ?>
                                        <div class="col-md-3 drop-me">
                                            <?php $this->load->view('Layouts/leftsidebar'); ?>
                                        </div>
                                    <?php } ?>
                                    <div class="col-md-12">
                                        <div class="login-formmea mb-3">
                                            <div class="box-widget widget-module">
                                                <div class="widget-head clearfix">
                                                    <span class="h-icon"><i class="fa fa-th"></i></span>
                                                    <h4>Search lead id?</h4>
                                                </div>
                                                <div class="widget-container">
                                                    <div class="widget-block">
                                                        <?php if ($this->session->flashdata('message')) { ?>
                                                            <div class="alert alert-success alert-dismissible">
                                                                <a href="#" class="close" data-dismiss="alert">&times;</a>
                                                                <strong><?= $this->session->flashdata('message'); ?></strong>
                                                            </div>
                                                        <?php } elseif ($this->session->flashdata('error')) { ?>
                                                            <div class="alert alert-danger alert-dismissible">
                                                                <a href="#" class="close" data-dismiss="alert">&times;</a>
                                                                <strong><?= $this->session->flashdata('error'); ?></strong>
                                                            </div>
                                                        <?php } ?>

                                                        <form id="leadIddata" autocomplete="off" action="<?= base_url('support/searchLeadAllocation'); ?>" method="POST">
                                                            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control" name="lead_id" id="lead_id" required
                                                                        value="<?= isset($_POST['lead_id']) ? $_POST['lead_id'] : ''; ?>"
                                                                        placeholder="Please enter lead id*"
                                                                        onkeypress="return !isNaN(String.fromCharCode(event.keyCode))">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <button type="submit" id="search_lead_id" class="btn btn-primary">Search LEAD ID</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (!empty($status)) { ?>
                                            <div class="login-formmea" id="lead_allocation_form">
                                                <div class="box-widget widget-module">
                                                    <div class="widget-head clearfix">
                                                        <span class="h-icon"><i class="fa fa-th"></i></span>
                                                        <h4>Update Lead Allocation Details</h4>
                                                    </div>
                                                    <div class="widget-container">
                                                        <div class="widget-block">
                                                            <form autocomplete="off" method="POST">
                                                                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                                                                <input type="hidden" name="lead_id" value="<?= $leadInfo['lead_id'] ?? 0; ?>">
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <label for="lead_screener_assign_user_id">Screener Users <span class="text-danger">*</span></label>
                                                                        <select class="form-control" name="user_id" required id="lead_screener_assign_user_id">
                                                                            <option value="">Select User</option>
                                                                            <?php foreach ($screener_list as $userVal) { ?>
                                                                                <option value="<?= $userVal['user_id']; ?>"
                                                                                    <?= ($leadInfo['lead_screener_assign_user_id'] == $userVal['user_id']) ? 'selected' : ''; ?>>
                                                                                    <?= $userVal['name'] . ' - ' . $userVal['user_id']; ?>
                                                                                </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label for="lead_credit_assign_user_id">Credit Users <span class="text-danger">*</span></label>
                                                                        <select class="form-control" name="user_id" required id="lead_credit_assign_user_id">
                                                                            <option value="">Select User</option>
                                                                            <?php foreach ($credit_list as $userVal) { ?>
                                                                                <option value="<?= $userVal['user_id']; ?>"
                                                                                    <?= ($leadInfo['lead_credit_assign_user_id'] == $userVal['user_id']) ? 'selected' : ''; ?>>
                                                                                    <?= $userVal['name'] . ' - ' . $userVal['user_id']; ?>
                                                                                </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label for="stage_id">Status <span class="text-danger">*</span></label>
                                                                        <select class="form-control" name="lead_status_id" id="lead_status_id">
                                                                            <option value="">Select Stage</option>
                                                                            <?php foreach ($lead_status as $statusVal) { ?>
                                                                                <option value="<?= $statusVal['status_id']; ?>"
                                                                                    <?= ($leadInfo['lead_status_id'] == $statusVal['status_id']) ? 'selected' : ''; ?>>
                                                                                    <?= $statusVal['status_name']; ?>
                                                                                </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                    <br />
                                                                    <div class="col-md-12 mt-3">
                                                                        <label for="lead_followup_remark">Lead Followup Remark</label>
                                                                        <textarea class="form-control" name="lead_followup_remark" id="lead_followup_remark"
                                                                            rows="3" placeholder="Please enter lead followup remark."></textarea>
                                                                    </div>
                                                                    <br />
                                                                    <div class="col-md-12 mt-3">
                                                                        <button type="button" class="btn btn-success update_lead_allocation">Update Lead Allocation</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('Layouts/footer'); ?>
    <?php $this->load->view('Support/support_js'); ?>
</section>
