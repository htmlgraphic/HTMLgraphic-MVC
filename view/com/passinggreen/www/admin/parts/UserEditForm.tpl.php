<form action="javascript:void(0);" method="post" id="frm-user-edit" class="dialog validated">
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
                        <input type="text" name="shipAddr2" value="" />
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
                        <select name="shipCountry" class="required">
                            <option value="">--Select--</option>
                            <?php foreach ($countries as $country) : ?>
                                <option value="<?= $country->getID() ?>"><?= $country->getCountry() ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col">
                        <label>Status:</label>
                        <select name="is_enabled" class="required">
                            <option value="yes">Enabled</option>
                            <option value="no">Disabled</option>
                        </select>
                        <label>Type:</label>
                        <select name="level" onchange="evt_levelChange(this);">
                            <option value="user">User</option>
                            <option value="member">Member</option>
                            <option value="admin">Admin</option>
                        </select>
                        <label>Password:</label>
                        <input type="text" name="passwd" value="" />
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
                        <label>Address 2:</label><input type="text" name="userAddr2" value="" />
                        <label>City:</label><input type="text" name="userCity" class="required" value="" />
                        <label>State:</label>
                        <select name="userState" class="required">
                            <option value="">--Select--</option>
                            <?php foreach ($provinces as $province) : ?>
                                <option value="<?= $province->getID() ?>"><?= $province->getProvince() ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>ZIP:</label><input type="text" name="userZip" class="required" value="" />
                        <label>Country:</label>
                        <select name="userCountry" class="required">
                            <option value="">--Select--</option>
                            <?php foreach ($countries as $country) : ?>
                                <option value="<?= $country->getID() ?>"><?= $country->getCountry() ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Phone:</label><input type="text" name="userAltPhone" class="required" value="" />
                        <label>Fax:</label><input type="text" name="userFax" value="" />
                    </div>
                    <div class="col">
                        <label>Company URL:</label><input type="text" name="web" value="" />
                        <label>Bio:</label>
                        <textarea name="userBio" rows="10" cols="10"></textarea>
                        <label>Category:</label>
                        <select name="userCompanyType[]">
                            <option value="">--Select--</option>
                            <?php foreach ($business_categories as $business_category) : ?>
                                <option value="<?= $business_category->getID() ?>"><?= $business_category->getCategory() ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Category:</label>
                        <select name="userCompanyType[]">
                            <option value="">--Select--</option>
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
                        <label>Payment Type:</label><input type="text" name="paymentType" value="" />
                        <label>Payment Notes:</label><input type="text" name="paymentTypeDetails" value="" />
                        <label>Payment Status:</label><input type="text" name="ccc_status"  value="" readonly="readonly" />
                        <label>Name:</label><input type="text" name="ccc_name"  value="" readonly="readonly" />
                        <label>Credit Card Status:</label><input type="text" name="ccc_tcode" value="" readonly="readonly" />
                        <label>Credit Card:</label><input type="text" name="ccc_ccNum" class="creditcard" value="" />
                        <label>Month:</label><input type="text" name="ccc_MM" class="digits" value="" />
                        <label>Year:</label><input type="text" name="ccc_YY" class="digits" value="" />
                        <label>Verification Code:</label><input type="text" class="digits" name="ccc_ccCode" value="" />
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
                        <input type="text" name="referralsPassed" value="" readonly="readonly" />
                        <label>Account Balance ($USD):</label>
                        <input type="text" name="accountBalance" value="" readonly="readonly" />
                        <label>Referral(s) Received:</label>
                        <input type="text" name="referralsReceived" value="" readonly="readonly" />
                        <label>Commission Due ($USD):</label>
                        <input type="text" name="commissionDue" value="" readonly="readonly" />
                    </div>
                </div>
            </div>
        </div>
    </fieldset>
</form>