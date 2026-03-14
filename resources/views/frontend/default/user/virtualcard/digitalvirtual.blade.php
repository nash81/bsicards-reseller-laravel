@extends('frontend::layouts.user')
@section('title')
    {{ __('Virtual MasterCard') }}
@endsection
@section('content')
    <div class="row">
        <!-- Get New Card Form -->
        <div class="col-xl-12">
            <div class="site-card">
                <div class="site-card-header">
                    <h3 class="title-small">{{ __('Get New Virtual MasterCard') }}</h3>
                </div>
                <div class="site-card-body">
                    <form action="{{ route('user.digitalnewvirtualcard') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Name on card') }}<span class="required">*</span></label>
                                    <input type="text" class="form-control" value="{{ $user->first_name }} {{ $user->last_name }}" readonly>
                                    <input type="hidden" name="nameoncard" value="{{ $user->first_name }} {{ $user->last_name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Email') }}<span class="required">*</span></label>
                                    <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                                    <input type="hidden" name="useremail" value="{{ $user->email }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Date Of Birth') }}<span class="required">*</span></label>
                                    <input type="date" class="form-control" name="dob" id="dob" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Phone Number') }}<span class="required">*</span></label>
                                    <input type="text" class="form-control" name="phone" placeholder="Enter phone number" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Country Code') }}<span class="required">*</span></label>
                                    <select class="form-control form-select" id="countrycode" name="countrycode" required>
                                        <option value="">{{ __('Select Code') }}</option>
                                        <option value="93">Afghanistan +93</option>
                                        <option value="355">Albania +355</option>
                                        <option value="213">Algeria +213</option>
                                        <option value="376">Andorra +376</option>
                                        <option value="244">Angola +244</option>
                                        <option value="1">Antigua and Barbuda +1</option>
                                        <option value="54">Argentina +54</option>
                                        <option value="374">Armenia +374</option>
                                        <option value="61">Australia +61</option>
                                        <option value="43">Austria +43</option>
                                        <option value="994">Azerbaijan +994</option>
                                        <option value="1">Bahamas +1</option>
                                        <option value="973">Bahrain +973</option>
                                        <option value="880">Bangladesh +880</option>
                                        <option value="1">Barbados +1</option>
                                        <option value="375">Belarus +375</option>
                                        <option value="32">Belgium +32</option>
                                        <option value="501">Belize +501</option>
                                        <option value="229">Benin +229</option>
                                        <option value="975">Bhutan +975</option>
                                        <option value="591">Bolivia +591</option>
                                        <option value="387">Bosnia and Herzegovina +387</option>
                                        <option value="267">Botswana +267</option>
                                        <option value="55">Brazil +55</option>
                                        <option value="673">Brunei +673</option>
                                        <option value="359">Bulgaria +359</option>
                                        <option value="226">Burkina Faso +226</option>
                                        <option value="257">Burundi +257</option>
                                        <option value="238">Cabo Verde +238</option>
                                        <option value="855">Cambodia +855</option>
                                        <option value="237">Cameroon +237</option>
                                        <option value="1">Canada +1</option>
                                        <option value="236">Central African Republic +236</option>
                                        <option value="235">Chad +235</option>
                                        <option value="56">Chile +56</option>
                                        <option value="86">China +86</option>
                                        <option value="57">Colombia +57</option>
                                        <option value="269">Comoros +269</option>
                                        <option value="242">Congo +242</option>
                                        <option value="243">Congo (DRC) +243</option>
                                        <option value="506">Costa Rica +506</option>
                                        <option value="385">Croatia +385</option>
                                        <option value="53">Cuba +53</option>
                                        <option value="357">Cyprus +357</option>
                                        <option value="420">Czech Republic +420</option>
                                        <option value="45">Denmark +45</option>
                                        <option value="253">Djibouti +253</option>
                                        <option value="1">Dominica +1</option>
                                        <option value="1">Dominican Republic +1</option>
                                        <option value="593">Ecuador +593</option>
                                        <option value="20">Egypt +20</option>
                                        <option value="503">El Salvador +503</option>
                                        <option value="240">Equatorial Guinea +240</option>
                                        <option value="291">Eritrea +291</option>
                                        <option value="372">Estonia +372</option>
                                        <option value="268">Eswatini +268</option>
                                        <option value="251">Ethiopia +251</option>
                                        <option value="679">Fiji +679</option>
                                        <option value="358">Finland +358</option>
                                        <option value="33">France +33</option>
                                        <option value="241">Gabon +241</option>
                                        <option value="220">Gambia +220</option>
                                        <option value="995">Georgia +995</option>
                                        <option value="49">Germany +49</option>
                                        <option value="233">Ghana +233</option>
                                        <option value="30">Greece +30</option>
                                        <option value="1">Grenada +1</option>
                                        <option value="502">Guatemala +502</option>
                                        <option value="224">Guinea +224</option>
                                        <option value="245">Guinea-Bissau +245</option>
                                        <option value="592">Guyana +592</option>
                                        <option value="509">Haiti +509</option>
                                        <option value="504">Honduras +504</option>
                                        <option value="36">Hungary +36</option>
                                        <option value="354">Iceland +354</option>
                                        <option value="91">India +91</option>
                                        <option value="62">Indonesia +62</option>
                                        <option value="98">Iran +98</option>
                                        <option value="964">Iraq +964</option>
                                        <option value="353">Ireland +353</option>
                                        <option value="972">Israel +972</option>
                                        <option value="39">Italy +39</option>
                                        <option value="1">Jamaica +1</option>
                                        <option value="81">Japan +81</option>
                                        <option value="962">Jordan +962</option>
                                        <option value="7">Kazakhstan +7</option>
                                        <option value="254">Kenya +254</option>
                                        <option value="686">Kiribati +686</option>
                                        <option value="383">Kosovo +383</option>
                                        <option value="965">Kuwait +965</option>
                                        <option value="996">Kyrgyzstan +996</option>
                                        <option value="856">Laos +856</option>
                                        <option value="371">Latvia +371</option>
                                        <option value="961">Lebanon +961</option>
                                        <option value="266">Lesotho +266</option>
                                        <option value="231">Liberia +231</option>
                                        <option value="218">Libya +218</option>
                                        <option value="423">Liechtenstein +423</option>
                                        <option value="370">Lithuania +370</option>
                                        <option value="352">Luxembourg +352</option>
                                        <option value="261">Madagascar +261</option>
                                        <option value="265">Malawi +265</option>
                                        <option value="60">Malaysia +60</option>
                                        <option value="960">Maldives +960</option>
                                        <option value="223">Mali +223</option>
                                        <option value="356">Malta +356</option>
                                        <option value="692">Marshall Islands +692</option>
                                        <option value="222">Mauritania +222</option>
                                        <option value="230">Mauritius +230</option>
                                        <option value="52">Mexico +52</option>
                                        <option value="691">Micronesia +691</option>
                                        <option value="373">Moldova +373</option>
                                        <option value="377">Monaco +377</option>
                                        <option value="976">Mongolia +976</option>
                                        <option value="382">Montenegro +382</option>
                                        <option value="212">Morocco +212</option>
                                        <option value="258">Mozambique +258</option>
                                        <option value="95">Myanmar +95</option>
                                        <option value="264">Namibia +264</option>
                                        <option value="674">Nauru +674</option>
                                        <option value="977">Nepal +977</option>
                                        <option value="31">Netherlands +31</option>
                                        <option value="64">New Zealand +64</option>
                                        <option value="505">Nicaragua +505</option>
                                        <option value="227">Niger +227</option>
                                        <option value="234">Nigeria +234</option>
                                        <option value="389">North Macedonia +389</option>
                                        <option value="47">Norway +47</option>
                                        <option value="968">Oman +968</option>
                                        <option value="92">Pakistan +92</option>
                                        <option value="680">Palau +680</option>
                                        <option value="507">Panama +507</option>
                                        <option value="675">Papua New Guinea +675</option>
                                        <option value="595">Paraguay +595</option>
                                        <option value="51">Peru +51</option>
                                        <option value="63">Philippines +63</option>
                                        <option value="48">Poland +48</option>
                                        <option value="351">Portugal +351</option>
                                        <option value="974">Qatar +974</option>
                                        <option value="40">Romania +40</option>
                                        <option value="7">Russia +7</option>
                                        <option value="250">Rwanda +250</option>
                                        <option value="1">Saint Kitts and Nevis +1</option>
                                        <option value="1">Saint Lucia +1</option>
                                        <option value="1">Saint Vincent and the Grenadines +1</option>
                                        <option value="685">Samoa +685</option>
                                        <option value="378">San Marino +378</option>
                                        <option value="239">Sao Tome and Principe +239</option>
                                        <option value="966">Saudi Arabia +966</option>
                                        <option value="221">Senegal +221</option>
                                        <option value="381">Serbia +381</option>
                                        <option value="248">Seychelles +248</option>
                                        <option value="232">Sierra Leone +232</option>
                                        <option value="65">Singapore +65</option>
                                        <option value="421">Slovakia +421</option>
                                        <option value="386">Slovenia +386</option>
                                        <option value="677">Solomon Islands +677</option>
                                        <option value="252">Somalia +252</option>
                                        <option value="27">South Africa +27</option>
                                        <option value="211">South Sudan +211</option>
                                        <option value="34">Spain +34</option>
                                        <option value="94">Sri Lanka +94</option>
                                        <option value="249">Sudan +249</option>
                                        <option value="597">Suriname +597</option>
                                        <option value="46">Sweden +46</option>
                                        <option value="41">Switzerland +41</option>
                                        <option value="963">Syria +963</option>
                                        <option value="886">Taiwan +886</option>
                                        <option value="992">Tajikistan +992</option>
                                        <option value="255">Tanzania +255</option>
                                        <option value="66">Thailand +66</option>
                                        <option value="670">Timor-Leste +670</option>
                                        <option value="228">Togo +228</option>
                                        <option value="676">Tonga +676</option>
                                        <option value="1">Trinidad and Tobago +1</option>
                                        <option value="216">Tunisia +216</option>
                                        <option value="90">Turkey +90</option>
                                        <option value="993">Turkmenistan +993</option>
                                        <option value="688">Tuvalu +688</option>
                                        <option value="256">Uganda +256</option>
                                        <option value="380">Ukraine +380</option>
                                        <option value="971">United Arab Emirates +971</option>
                                        <option value="44">United Kingdom +44</option>
                                        <option value="1">United States +1</option>
                                        <option value="598">Uruguay +598</option>
                                        <option value="998">Uzbekistan +998</option>
                                        <option value="678">Vanuatu +678</option>
                                        <option value="39">Vatican City +39</option>
                                        <option value="58">Venezuela +58</option>
                                        <option value="84">Vietnam +84</option>
                                        <option value="967">Yemen +967</option>
                                        <option value="260">Zambia +260</option>
                                        <option value="263">Zimbabwe +263</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Country') }}<span class="required">*</span></label>
                                    <select class="form-control form-select" id="country" name="country" required>
                                        <option value="">{{ __('Select Country') }}</option>
                                        <option value="AF">Afghanistan</option>
                                        <option value="AL">Albania</option>
                                        <option value="DZ">Algeria</option>
                                        <option value="AD">Andorra</option>
                                        <option value="AO">Angola</option>
                                        <option value="AG">Antigua and Barbuda</option>
                                        <option value="AR">Argentina</option>
                                        <option value="AM">Armenia</option>
                                        <option value="AU">Australia</option>
                                        <option value="AT">Austria</option>
                                        <option value="AZ">Azerbaijan</option>
                                        <option value="BS">Bahamas</option>
                                        <option value="BH">Bahrain</option>
                                        <option value="BD">Bangladesh</option>
                                        <option value="BB">Barbados</option>
                                        <option value="BY">Belarus</option>
                                        <option value="BE">Belgium</option>
                                        <option value="BZ">Belize</option>
                                        <option value="BJ">Benin</option>
                                        <option value="BT">Bhutan</option>
                                        <option value="BO">Bolivia</option>
                                        <option value="BA">Bosnia and Herzegovina</option>
                                        <option value="BW">Botswana</option>
                                        <option value="BR">Brazil</option>
                                        <option value="BN">Brunei</option>
                                        <option value="BG">Bulgaria</option>
                                        <option value="BF">Burkina Faso</option>
                                        <option value="BI">Burundi</option>
                                        <option value="CV">Cabo Verde</option>
                                        <option value="KH">Cambodia</option>
                                        <option value="CM">Cameroon</option>
                                        <option value="CA">Canada</option>
                                        <option value="CF">Central African Republic</option>
                                        <option value="TD">Chad</option>
                                        <option value="CL">Chile</option>
                                        <option value="CN">China</option>
                                        <option value="CO">Colombia</option>
                                        <option value="KM">Comoros</option>
                                        <option value="CG">Congo</option>
                                        <option value="CD">Congo (DRC)</option>
                                        <option value="CR">Costa Rica</option>
                                        <option value="HR">Croatia</option>
                                        <option value="CU">Cuba</option>
                                        <option value="CY">Cyprus</option>
                                        <option value="CZ">Czech Republic</option>
                                        <option value="DK">Denmark</option>
                                        <option value="DJ">Djibouti</option>
                                        <option value="DM">Dominica</option>
                                        <option value="DO">Dominican Republic</option>
                                        <option value="EC">Ecuador</option>
                                        <option value="EG">Egypt</option>
                                        <option value="SV">El Salvador</option>
                                        <option value="GQ">Equatorial Guinea</option>
                                        <option value="ER">Eritrea</option>
                                        <option value="EE">Estonia</option>
                                        <option value="SZ">Eswatini</option>
                                        <option value="ET">Ethiopia</option>
                                        <option value="FJ">Fiji</option>
                                        <option value="FI">Finland</option>
                                        <option value="FR">France</option>
                                        <option value="GA">Gabon</option>
                                        <option value="GM">Gambia</option>
                                        <option value="GE">Georgia</option>
                                        <option value="DE">Germany</option>
                                        <option value="GH">Ghana</option>
                                        <option value="GR">Greece</option>
                                        <option value="GD">Grenada</option>
                                        <option value="GT">Guatemala</option>
                                        <option value="GN">Guinea</option>
                                        <option value="GW">Guinea-Bissau</option>
                                        <option value="GY">Guyana</option>
                                        <option value="HT">Haiti</option>
                                        <option value="HN">Honduras</option>
                                        <option value="HU">Hungary</option>
                                        <option value="IS">Iceland</option>
                                        <option value="IN">India</option>
                                        <option value="ID">Indonesia</option>
                                        <option value="IR">Iran</option>
                                        <option value="IQ">Iraq</option>
                                        <option value="IE">Ireland</option>
                                        <option value="IL">Israel</option>
                                        <option value="IT">Italy</option>
                                        <option value="JM">Jamaica</option>
                                        <option value="JP">Japan</option>
                                        <option value="JO">Jordan</option>
                                        <option value="KZ">Kazakhstan</option>
                                        <option value="KE">Kenya</option>
                                        <option value="KI">Kiribati</option>
                                        <option value="XK">Kosovo</option>
                                        <option value="KW">Kuwait</option>
                                        <option value="KG">Kyrgyzstan</option>
                                        <option value="LA">Laos</option>
                                        <option value="LV">Latvia</option>
                                        <option value="LB">Lebanon</option>
                                        <option value="LS">Lesotho</option>
                                        <option value="LR">Liberia</option>
                                        <option value="LY">Libya</option>
                                        <option value="LI">Liechtenstein</option>
                                        <option value="LT">Lithuania</option>
                                        <option value="LU">Luxembourg</option>
                                        <option value="MG">Madagascar</option>
                                        <option value="MW">Malawi</option>
                                        <option value="MY">Malaysia</option>
                                        <option value="MV">Maldives</option>
                                        <option value="ML">Mali</option>
                                        <option value="MT">Malta</option>
                                        <option value="MH">Marshall Islands</option>
                                        <option value="MR">Mauritania</option>
                                        <option value="MU">Mauritius</option>
                                        <option value="MX">Mexico</option>
                                        <option value="FM">Micronesia</option>
                                        <option value="MD">Moldova</option>
                                        <option value="MC">Monaco</option>
                                        <option value="MN">Mongolia</option>
                                        <option value="ME">Montenegro</option>
                                        <option value="MA">Morocco</option>
                                        <option value="MZ">Mozambique</option>
                                        <option value="MM">Myanmar</option>
                                        <option value="NA">Namibia</option>
                                        <option value="NR">Nauru</option>
                                        <option value="NP">Nepal</option>
                                        <option value="NL">Netherlands</option>
                                        <option value="NZ">New Zealand</option>
                                        <option value="NI">Nicaragua</option>
                                        <option value="NE">Niger</option>
                                        <option value="NG">Nigeria</option>
                                        <option value="MK">North Macedonia</option>
                                        <option value="NO">Norway</option>
                                        <option value="OM">Oman</option>
                                        <option value="PK">Pakistan</option>
                                        <option value="PW">Palau</option>
                                        <option value="PA">Panama</option>
                                        <option value="PG">Papua New Guinea</option>
                                        <option value="PY">Paraguay</option>
                                        <option value="PE">Peru</option>
                                        <option value="PH">Philippines</option>
                                        <option value="PL">Poland</option>
                                        <option value="PT">Portugal</option>
                                        <option value="QA">Qatar</option>
                                        <option value="RO">Romania</option>
                                        <option value="RU">Russia</option>
                                        <option value="RW">Rwanda</option>
                                        <option value="KN">Saint Kitts and Nevis</option>
                                        <option value="LC">Saint Lucia</option>
                                        <option value="VC">Saint Vincent and the Grenadines</option>
                                        <option value="WS">Samoa</option>
                                        <option value="SM">San Marino</option>
                                        <option value="ST">Sao Tome and Principe</option>
                                        <option value="SA">Saudi Arabia</option>
                                        <option value="SN">Senegal</option>
                                        <option value="RS">Serbia</option>
                                        <option value="SC">Seychelles</option>
                                        <option value="SL">Sierra Leone</option>
                                        <option value="SG">Singapore</option>
                                        <option value="SK">Slovakia</option>
                                        <option value="SI">Slovenia</option>
                                        <option value="SB">Solomon Islands</option>
                                        <option value="SO">Somalia</option>
                                        <option value="ZA">South Africa</option>
                                        <option value="SS">South Sudan</option>
                                        <option value="ES">Spain</option>
                                        <option value="LK">Sri Lanka</option>
                                        <option value="SD">Sudan</option>
                                        <option value="SR">Suriname</option>
                                        <option value="SE">Sweden</option>
                                        <option value="CH">Switzerland</option>
                                        <option value="SY">Syria</option>
                                        <option value="TW">Taiwan</option>
                                        <option value="TJ">Tajikistan</option>
                                        <option value="TZ">Tanzania</option>
                                        <option value="TH">Thailand</option>
                                        <option value="TL">Timor-Leste</option>
                                        <option value="TG">Togo</option>
                                        <option value="TO">Tonga</option>
                                        <option value="TT">Trinidad and Tobago</option>
                                        <option value="TN">Tunisia</option>
                                        <option value="TR">Turkey</option>
                                        <option value="TM">Turkmenistan</option>
                                        <option value="TV">Tuvalu</option>
                                        <option value="UG">Uganda</option>
                                        <option value="UA">Ukraine</option>
                                        <option value="AE">United Arab Emirates</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="US">United States</option>
                                        <option value="UY">Uruguay</option>
                                        <option value="UZ">Uzbekistan</option>
                                        <option value="VU">Vanuatu</option>
                                        <option value="VA">Vatican City</option>
                                        <option value="VE">Venezuela</option>
                                        <option value="VN">Vietnam</option>
                                        <option value="YE">Yemen</option>
                                        <option value="ZM">Zambia</option>
                                        <option value="ZW">Zimbabwe</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Address') }}<span class="required">*</span></label>
                                    <input type="text" class="form-control" name="address1" placeholder="Enter address" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('City') }}<span class="required">*</span></label>
                                    <input type="text" class="form-control" name="city" placeholder="Enter city" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('State') }}<span class="required">*</span></label>
                                    <input type="text" class="form-control" name="state" placeholder="Enter state" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Zip Code') }}<span class="required">*</span></label>
                                    <input type="text" class="form-control" name="postalcode" placeholder="Enter zip code" required>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i data-lucide="info"></i>
                            {{ __('Virtual MasterCard Issuance Fee') }}: <strong>$ {{ $general->digifee }}</strong> {{ __('will be debited from your balance') }}
                        </div>

                        <div class="action-btns mt-3">
                            <button type="submit" class="site-btn primary-btn">
                                <i data-lucide="credit-card"></i> {{ __('Proceed to Issue Card') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Issued Cards Table -->
        <div class="col-xl-12">
            <div class="site-card">
                <div class="site-card-header">
                    <h3 class="title-small">{{ __('Your Issued Virtual MasterCards') }}</h3>
                </div>
                <div class="site-card-body p-0">
                    <div class="site-custom-table">
                        <div class="contents">
                            <div class="site-table-list site-table-head">
                                <div class="site-table-col">{{ __('Card ID') }}</div>
                                <div class="site-table-col">{{ __('Name on Card') }}</div>
                                <div class="site-table-col">{{ __('Last 4 Digits') }}</div>
                                <div class="site-table-col">{{ __('Action') }}</div>
                            </div>
                            @forelse ($virtualcards->data as $item)
                            <div class="site-table-list">
                                <div class="site-table-col">
                                    <div class="description">
                                        <div class="event-icon">
                                            <i data-lucide="credit-card"></i>
                                        </div>
                                        <div class="content">
                                            <div class="title">{{ $item->cardid }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="site-table-col">
                                    <div class="trx">{{ $item->nameoncard }}</div>
                                </div>
                                <div class="site-table-col">
                                    <span class="site-badge badge-primary">**** {{ $item->lastfour ?? '' }}</span>
                                </div>
                                <div class="site-table-col">
                                    <div class="action">
                                        <a href="{{ route('user.getdigitalcard',$item->cardid) }}" class="icon-btn">
                                            <i data-lucide="eye"></i>{{ __('View Card') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="no-data-found">{{ __('No Cards Found') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

