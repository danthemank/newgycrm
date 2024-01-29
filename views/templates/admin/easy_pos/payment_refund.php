<div id="payments_list">
    <h1>Payments</h1>
    <div class="global notice notice-warning is-dismissible hidden">Error: Invalid refund amount.</div>
    <div class="error notice notice-warning is-dismissible hidden">Error</div>
    <div class="notice notice-success is-dismissible hidden">Success: </div>
    <table class="wp-list-table widefat fixed posts centered-table">
        <thead>
            <tr>
                <th>Amount</th>
                <th>Date</th>
                <th>Payment Method</th>
                <th>View</th>
            </tr>
        </thead>
        <tbody>
            <?= $this->get_payments() ?>
        </tbody>
    </table>
</div>

<div class="hidden" id="view_payment">
    <div class="modal-header">
        <h3>Payment Details</h3>
    </div>
    <hr class="divider">
    <form method="POST" action="" class="payment-details-header">
        <input type="hidden" name="payment_order_id" id="payment_order_id"/>
        <input type="hidden" name="parent_order_id" id="parent_order_id"/>
        <input type="hidden" name="payment_method" id="payment_method"/>
        <div class="flex-container">
            <div><svg viewBox="0 0 24 24" width="45px" height="45px" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M18 8.5V8.35417C18 6.50171 16.4983 5 14.6458 5H9.5C7.567 5 6 6.567 6 8.5C6 10.433 7.567 12 9.5 12H14.5C16.433 12 18 13.567 18 15.5C18 17.433 16.433 19 14.5 19H9.42708C7.53436 19 6 17.4656 6 15.5729V15.5M12 3V21" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg></div>
            <div>
                <h4 class="order-id-title">Payment on Order<span class="order-id"> #<span id="order_title_id"></span></span> - <a href="" target="_blank" id="customer_name"></a></h4>
                <div class="flex-container payment-details">
                    <div>Source: <span id="payment_source"></span></div>
                    <div>-</div>
                    <div>At: <span id="payment_date"></span></div>
                </div>
            </div>
        </div>

        <div class="payment-details-body">
            <div class="flex-container">
                <div class="pay-method">
                    <h4 class="order-id-title payment-details">Pay Method</h4>
                    <div class="flex-container">
                        <div><svg viewBox="0 0 24 24" width="30px" height="30px" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M4 7C4 6.44772 4.44772 6 5 6H19C19.5523 6 20 6.44772 20 7V8H19.9993H19.9614H19.9235H19.8857H19.8479H19.8102H19.7725H19.7348H19.6972H19.6596H19.622H19.5845H19.547H19.5096H19.4722H19.4349H19.3975H19.3603H19.323H19.2858H19.2487H19.2115H19.1744H19.1374H19.1004H19.0634H19.0264H18.9895H18.9527H18.9158H18.879H18.8423H18.8056H18.7689H18.7322H18.6956H18.659H18.6225H18.5859H18.5495H18.513H18.4766H18.4402H18.4039H18.3676H18.3313H18.295H18.2588H18.2226H18.1865H18.1504H18.1143H18.0782H18.0422H18.0062H17.9703H17.9343H17.8984H17.8626H17.8268H17.7909H17.7552H17.7194H17.6837H17.648H17.6124H17.5768H17.5412H17.5056H17.4701H17.4346H17.3991H17.3636H17.3282H17.2928H17.2575H17.2221H17.1868H17.1515H17.1163H17.0811H17.0458H17.0107H16.9755H16.9404H16.9053H16.8702H16.8352H16.8002H16.7652H16.7302H16.6953H16.6604H16.6255H16.5906H16.5557H16.5209H16.4861H16.4514H16.4166H16.3819H16.3472H16.3125H16.2778H16.2432H16.2086H16.174H16.1394H16.1049H16.0704H16.0359H16.0014H15.9669H15.9325H15.8981H15.8637H15.8293H15.795H15.7606H15.7263H15.692H15.6577H15.6235H15.5892H15.555H15.5208H15.4866H15.4525H15.4183H15.3842H15.3501H15.316H15.282H15.2479H15.2139H15.1798H15.1458H15.1119H15.0779H15.0439H15.01H14.9761H14.9422H14.9083H14.8744H14.8406H14.8067H14.7729H14.7391H14.7053H14.6715H14.6377H14.604H14.5702H14.5365H14.5028H14.4691H14.4354H14.4017H14.3681H14.3344H14.3008H14.2672H14.2336H14.2H14.1664H14.1328H14.0992H14.0657H14.0322H13.9986H13.9651H13.9316H13.8981H13.8646H13.8311H13.7977H13.7642H13.7308H13.6973H13.6639H13.6305H13.597H13.5636H13.5302H13.4969H13.4635H13.4301H13.3967H13.3634H13.33H13.2967H13.2634H13.23H13.1967H13.1634H13.1301H13.0968H13.0635H13.0302H12.9969H12.9636H12.9303H12.8971H12.8638H12.8305H12.7973H12.764H12.7308H12.6975H12.6643H12.6311H12.5978H12.5646H12.5314H12.4981H12.4649H12.4317H12.3985H12.3653H12.3321H12.2989H12.2656H12.2324H12.1992H12.166H12.1328H12.0996H12.0664H12.0332H12H11.9668H11.9336H11.9004H11.8672H11.834H11.8008H11.7676H11.7344H11.7011H11.6679H11.6347H11.6015H11.5683H11.5351H11.5019H11.4686H11.4354H11.4022H11.3689H11.3357H11.3025H11.2692H11.236H11.2027H11.1695H11.1362H11.1029H11.0697H11.0364H11.0031H10.9698H10.9365H10.9032H10.8699H10.8366H10.8033H10.77H10.7366H10.7033H10.67H10.6366H10.6033H10.5699H10.5365H10.5031H10.4698H10.4364H10.403H10.3695H10.3361H10.3027H10.2692H10.2358H10.2023H10.1689H10.1354H10.1019H10.0684H10.0349H10.0014H9.96784H9.9343H9.90075H9.86719H9.83361H9.80003H9.76643H9.73282H9.69919H9.66556H9.63191H9.59825H9.56458H9.53089H9.49719H9.46348H9.42975H9.39601H9.36226H9.32849H9.29471H9.26091H9.2271H9.19327H9.15943H9.12558H9.0917H9.05782H9.02391H8.98999H8.95606H8.92211H8.88814H8.85415H8.82015H8.78613H8.7521H8.71804H8.68397H8.64989H8.61578H8.58165H8.54751H8.51335H8.47917H8.44497H8.41076H8.37652H8.34226H8.30799H8.27369H8.23938H8.20505H8.17069H8.13631H8.10192H8.0675H8.03306H7.99861H7.96413H7.92963H7.8951H7.86056H7.82599H7.7914H7.75679H7.72216H7.6875H7.65282H7.61812H7.58339H7.54864H7.51387H7.47907H7.44425H7.40941H7.37454H7.33965H7.30473H7.26978H7.23482H7.19982H7.1648H7.12976H7.09469H7.05959H7.02447H6.98932H6.95415H6.91895H6.88372H6.84846H6.81318H6.77787H6.74254H6.70717H6.67178H6.63636H6.60091H6.56543H6.52992H6.49439H6.45882H6.42323H6.38761H6.35196H6.31627H6.28056H6.24482H6.20905H6.17325H6.13741H6.10155H6.06566H6.02973H5.99377H5.95779H5.92177H5.88571H5.84963H5.81351H5.77737H5.74118H5.70497H5.66872H5.63245H5.59613H5.55979H5.52341H5.48699H5.45054H5.41406H5.37755H5.341H5.30441H5.26779H5.23113H5.19444H5.15772H5.12096H5.08416H5.04733H5.01046H4.97355H4.93661H4.89963H4.86261H4.82556H4.78847H4.75134H4.71418H4.67698H4.63974H4.60246H4.56514H4.52779H4.49039H4.45296H4.41549H4.37798H4.34043H4.30284H4.26521H4.22754H4.18983H4.15208H4.11429H4.07646H4.03859H4.00068H4V7ZM4 18V11H20V18C20 18.5523 19.5523 19 19 19H5C4.44772 19 4 18.5523 4 18ZM5 4C3.34315 4 2 5.34315 2 7V9V10V18C2 19.6569 3.34315 21 5 21H19C20.6569 21 22 19.6569 22 18V10V9V7C22 5.34315 20.6569 4 19 4H5ZM14 14C13.4477 14 13 14.4477 13 15C13 15.5523 13.4477 16 14 16H18C18.5523 16 19 15.5523 19 15C19 14.4477 18.5523 14 18 14H14Z" fill="#000000"></path> </g></svg></div>
                        <div>
                            <p id="pay_method_name"></p>
                            <p class="hidden pay-method-id">Ending in <span id="pay_method_id"></span></p>
                        </div>
                    </div>
                </div>
                <div class="payment-amount">
                    <h4 class="order-id-title payment-details">Paid</h4>
                    <span class="amount_item">$<span id="payment_amount"></span></span>
                </div>
                <div class="transaction-id">
                    <h4 class="order-id-title payment-details">Transaction ID</h4>
                    <a target="_blank" href="" id="transaction_id"></a>
                </div>
                <div class="transaction-fee">
                    <h4 class="order-id-title payment-details">Discounts/Fees</h4>
                    <span class="amount_item">$<span class="transaction_fee"></span></span>
                </div>
            </div>
            <div class="description">
                <h4 class="order-id-title payment-details">Description</h4>
                <span id="description"></span>
            </div>
        </div>

        <div class="payment-refund-options refund-item hidden">
            <h4 class="payment-details">Refund Options</h4>
            <div class="refund-options">
                <div>
                    <input type="radio" name="type_refund" id="full_refund" checked>
                    <label for="full_refund">Full Refund</label>
                </div>
                <div class="partial-refund">
                    <input type="radio" name="type_refund" value="partial_refund" id="partial_refund">
                    <label for="partial_refund">Partial Refund</label>
                </div>
            </div>
            <div class="partial-refund-item hidden">
                <div>
                    <label for="refund_amount">Refund amount</label>
                    <input type="number" name="refund_amount" step="0.1" id="refund_amount">
                </div>
            </div>
            <div class="refund-items">
                <div>Amount already refunded:&nbsp;&nbsp;&nbsp;&nbsp;<span class="refunded-total-item"></span></div>
                <div>Total available to refund:&nbsp;&nbsp;&nbsp;&nbsp;<span class="net-total-item"></span></div>
            </div>
        </div>

        <div class="payment-refunded hidden">
            <div class="flex-container refunded-total">Refunded:<span class="refunded-total-item" id="refunded_total"></span></div>
            <div class="flex-container net-payment">Net Payment:<span class="net-total-item" id="net_payment"></span></div>
        </div>

        <div class="payment-details-items hidden">
            <h4 class="payment-details">Items</h4>
            <table class="wp-list-table widefat fixed posts centered-table">
                <thead>
                    <tr>
                        <th class="hidden refund-item">Refund</th>
                        <th>Item</th>
                        <th>Amount ($)</th>
                    </tr>
                </thead>
                <tbody id="order_items">
                </tbody>
            </table>
        </div>

        
        <div class="payment-details-result hidden refund-item">
            <h4 class="payment-details" style="display: inline">Refund Amount: <span>$<span id="result_refund_amount"></span></span></h4>
            <hr class="divider">
        </div>

        <div class="payment-electronic-processing hidden refund-item">
            <div>
                <input type="checkbox" name="electronic_processing" id="electronic_processing" value="1">
                <label for="electronic_processing">Skip electronic processing</label>
            </div>
            <div class="disclaimer">
                <div class="flex-container">
                    <svg viewBox="0 0 1024 1024" width="20px" height="20px" xmlns="http://www.w3.org/2000/svg" fill="#ff0000" stroke="#ff0000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path fill="#ff0000" d="M512 64a448 448 0 1 1 0 896 448 448 0 0 1 0-896zm0 192a58.432 58.432 0 0 0-58.24 63.744l23.36 256.384a35.072 35.072 0 0 0 69.76 0l23.296-256.384A58.432 58.432 0 0 0 512 256zm0 512a51.2 51.2 0 1 0 0-102.4 51.2 51.2 0 0 0 0 102.4z"></path></g></svg>
                    <span>Electronic processing will be perfomed</span>
                </div>
            </div>
        </div>

        <div class="payment-details-footer">
            <button type="button" class="refund-btn" id="refund_options">Refund</button>
            <button type="submit" class="refund-btn hidden refund-item">Refund</button>
            <button type="button" class="refund-btn hidden refund-cancel cancel-btn">Cancel</button>
        </div>
    </form>
    <div class="absolute hidden">
        <div class="lds-ring">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
</div>