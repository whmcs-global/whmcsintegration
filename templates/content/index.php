<style>

    table {
        color: #333;
        font-family: sans-serif;
        font-size: .9em;
        font-weight: 300;
        text-align: left;
        line-height: 40px;
        border-spacing: 0;
        border: 1px solid #428bca;
        width: 100%;
        margin: 20px auto;
        border-radius: 5px;
    }

    thead tr:first-child {
        background: #428bca;
        color: #fff;
        border: none;
    }

    th {font-weight: bold;}
    th:first-child, td:first-child {padding: 0 15px 0 20px;}

    thead tr:last-child th {border-bottom: 2px solid #ddd;}

    tbody tr:hover {background-color: #f0fbff;}
    tbody tr:last-child td {border: none;}
    tbody td {border-bottom: 1px solid #ddd;}

    td:last-child {
        /*text-align: right;*/
        padding-right: 10px;
    }

    .button {
        color: #696969;
        padding-right: 5px;
        cursor: pointer;
    }

    .alterar:hover {
        color: #428bca;
    }

    .excluir:hover {
        color: #dc2a2a;
    }
    .info, .success, .warning, .error, .validation {
        /*border: 1px solid;*/
        margin: 10px 0px;
        padding:15px 10px 15px 15px;
        background-repeat: no-repeat;
        background-position: 10px center;
        border-radius: 5px;
    }
    .info {
        color: #00529B;
        background-color: #BDE5F8;
    }
    .success {
        color: #4F8A10;
        background-color: #DFF2BF;
    }
    .warning {
        color: #9F6000;
        background-color: #FEEFB3;
    }
    .error {
        color: #D8000C;
        background-color: #FFBABA;
    }
    @media only screen and (min-width: 1024px){
        #app-navigation{
            position: fixed !important;
            height: var(--body-height) !important;
        }
            #app-content{
                margin-left: 330px !important;
            }
    }

</style>
<div id="app-navigation-toggle" class="icon-menu" style="display:none;"></div>
<?php if (count($_['groups']) == 0) { ?>
    <div id="emptycontent">
        <div class="icon-activity"></div>
        <h2>No Groups added yet</h2>
        <p>Here you can manage Account Limit for available Groups</p>
    </div>
<?php } ?>
<?php if (count($_['groups'])) { ?>
    <div id="container" data-activity-filter="all">

        <div class="section activity-section group">
            <h2>Groups for Account Limit Setting
               <!-- <span class="tooltip" original-title="">Groups for Account Limit Setting</span>-->
            </h2>
            <div class="info"><b>Note:</b> If you try to add existing users to a group beyond its limit. Then user will be deleted without any notification.<br>
                0 value stands for default behaviour for NextCloud groups.<br>
                If defined, You can't create user beyond group limit.</div>
            <?php //p(\OC::$server->getURLGenerator()->getAbsoluteURL('/index.php/apps/whmcs_integration/')) ?>
            <form method="post" action="<?php p(OC::$WEBROOT) ?>/index.php/apps/whmcsintegration">
                <div class="boxcontainer">

                    <div class="box">
                        <div class="messagecontainer">
                            <div class="activity-icon svg">
                                <?php if (isset($_POST["saveAccountLimit"])) { ?>
                                    <div class="success">Changes Saved Successfully</div>
                                <?php } ?>
                                <input type="submit" name="saveAccountLimit" value="Save Changes" style="float:right;">
                            </div>

                            <div class="activitysubject">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Group Name</th>
                                            <th>Account Limit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_['groups'] as $group) { ?>
                                            <tr>
                                                <td><?php p($group['group']) ?></td>
                                                <td><input type="text" name="gaccountlimit[<?php p($group['group']) ?>]"
                                                    <?php if ($group['limit'] > 0) { ?>
                                                               value="<?php p($group['limit']) ?>"
                                                           <?php } ?>
                                                           <?php if ($group['limit'] == 0) { ?>
                                                               placeholder="<?php p($group['limit']) ?>"
                                                           <?php } ?>
                                                           ></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>

                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </form>

        </div>



    </div>
<?php } ?>




<div id="loading_activities" class="icon-loading hidden"></div>