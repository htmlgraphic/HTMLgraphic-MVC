<form action="/admin-new/rpc/Admin/User/Add" method="post" id="frm-user-edit" class="dialog validated">
    <fieldset class="hidden">
        <input type="hidden" name="id" value="" />
    </fieldset>
    <fieldset>
        <div id="user-edit-tabs" class="tabs">
            <ul>
                <li><a href="#new-tab-1">User</a></li>
                <li><a href="#new-tab-2">Business</a></li>
                <li><a href="#new-tab-3">Payment</a></li>
                <li><a href="#new-tab-4">Referrals</a></li>
            </ul>
            <div id="new-tab-1">
                <div class="clear">
                    <div class="col">
                        <label>First Name:</label><input type="text" name="userFirstname" class="required" value="" />
                        <label>Last Name:</label><input type="text" name="userLastname" class="required" value="" />
                        <label>Email Address:</label><input type="text" name="useremail" class="required email" value="" />
                        <label>Phone:</label><input type="text" name="userPhone" class="required" value="" />
                        <label>Address:</label><input type="text" name="shipAddr1" class="required" value="" /><br />
                        <input type="text" name="shipAddr2" class="required" value="" />
                        <label>City:</label><input type="text" name="shipCity" class="required" value="" />
                        <label>State:</label>
                        <select name="shipState" class="required">
                            <option value="">--Select--</option>
                            <?php foreach ($provinces as $province) : ?>
                                <option value="<?= $province->getID() ?>"><?= $province->getProvince() ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>ZIP:</label><input type="text" name="shipZip" class="required" value="" />
                        <label>Country:</label>
                        <select name="shipCountry">
                            <option value="">--Select--</option>
                            <?php foreach ($countries as $country) : ?>
                                <option value="<?= $country->getID() ?>"><?= $country->getCountry() ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col">						<label>Status:</label>
                        <select name="is_enabled">
                            <option value="yes">Enabled</option>
                            <option value="no">Disabled</option>
                        </select>
                        <label>Type:</label>
                        <select name="level">
                            <option value="user">User</option>
                            <option value="member">Member</option>
                            <option value="admin">Admin</option>
                        </select>
                        <label>Updates? <input type="checkbox" name="updates" value="yes" /></label>
                        <label>&nbsp;</label>
                        <label>Site Areas:</label>
                        <label>Admin? <input type="checkbox" name="siteAreas[]" value="admin" /></label>
                        <label>Contact? <input type="checkbox" name="siteAreas[]" value="contact" /></label>
                        <label>&nbsp;</label>
                        <label>Balance (USD):</label> $<input type="text" name="balance" value="0.00" readonly="readonly"/>
                    </div>
                </div>
            </div>
            <div id="new-tab-2">
                <div class="clear">
                    <div class="col">
                        <label>Company Name:</label><input type="text" name="userCompany" class="required" value="" />
                        <label>Address 1:</label><input type="text" name="userAddr1" class="required" value="" />
                        <label>Address 2:</label><input type="text" name="userAddr2" class="" value="" />
                        <label>City:</label><input type="text" name="userCity" class="required" value="" />
                        <label>State:</label>
                        <select name="userState">
                            <option value="">--Select--</option>
                            <?php foreach ($provinces as $province) : ?>
                                <option value="<?= $province->getID() ?>"><?= $province->getProvince() ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>ZIP:</label><input type="text" name="userZip" class="required" value="" />
                        <label>Country:</label>
                        <select name="userCountry">
                            <option value="">--Select--</option>
                            <?php foreach ($countries as $country) : ?>
                                <option value="<?= $country->getID() ?>"><?= $country->getCountry() ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Phone:</label><input type="text" name="userAltPhone" class="required" value="" />
                        <label>Fax:</label><input type="text" name="userFax" class="" value="" />
                    </div>
                    <div class="col">
                        <label>Company URL:</label><input type="text" name="web" class="" value="" />
                        <label>Bio:</label>
                        <textarea name="userBio" rows="10" cols="10"></textarea>
                        <label>Category:</label>
                        <select name="userCompanyType[]">
                            <?php foreach ($business_categories as $business_category) : ?>
                                <option value="<?= $business_category->getID() ?>"><?= $business_category->getCategory() ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Category:</label>
                        <select name="userCompanyType[]">
                            <?php foreach ($business_categories as $business_category) : ?>
                                <option value="<?= $business_category->getID() ?>"><?= $business_category->getCategory() ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div id="new-tab-3">
                <div class="clear">
                    <div class="col">
                        <label>Payment Type:</label><input type="text" name="paymentType" class="" value="" />
                        <label>Payment Notes:</label><input type="text" name="paymentTypeDetails" class="" value="" />
                        <label>Payment Status:</label><input type="text" name="ccc_status" class="" value="" readonly="readonly" />
                        <label>Name:</label><input type="text" name="ccc_name" class="" value="" readonly="readonly" />
                        <label>Credit Card Status:</label><input type="text" name="ccc_tcode" class="" value="" readonly="readonly" />
                        <label>Credit Card:</label><input type="text" name="ccc_ccNum" class="" value="" readonly="readonly" />
                        <label>Month:</label><input type="text" name="ccc_MM" class="" value="" readonly="readonly" />
                        <label>Year:</label><input type="text" name="ccc_YY" class="" value="" readonly="readonly" />
                        <label>Verification Code:</label><input type="text" name="ccc_ccCode" class="" value="" readonly="readonly" />
                    </div>
                    <div class="col">
                        <label>CC Column Value &quot;Details&quot;</label>
                        <textarea rows="20" cols="20" name="ccc" readonly="readonly"></textarea>
                    </div>
                </div>
            </div>
            <div id="new-tab-4">
                <div class="clear">
                    <div class="col">
                        <label>Referral(s) Passed:</label>
                        <input type="text" name="referralsPassed" class="" value="" readonly="readonly" />
                        <label>Account Balance ($USD):</label>
                        <input type="text" name="accountBalance" class="" value="" readonly="readonly" />
                        <label>Referral(s) Received:</label>
                        <input type="text" name="referralsReceived" class="" value="" readonly="readonly" />
                        <label>Commission Due ($USD):</label>
                        <input type="text" name="commissionDue" class="" value="" readonly="readonly" />
                    </div>                    
                </div>
            </div>
        </div>
    </fieldset>
</form>