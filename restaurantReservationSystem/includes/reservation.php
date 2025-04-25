<section id="reservations" class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Make a Reservation</h2>
                <div class="title-underline"></div>
                <p class="section-description">
                    Reserve your table online for a seamless dining
                    experience. We look forward to serving you.
                </p>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-md-6 p-5">
                                <h3 class="mb-4">Book Your Table</h3>
                                <form
                                    id="reservationForm"
                                    action="process_reservation.php"
                                    method="post"
                                >
                                    <div class="form-group mb-3">
                                        <label
                                            for="name"
                                            class="form-label"
                                            >Full Name</label
                                        >
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="name"
                                            name="name"
                                            placeholder="Your Name"
                                            required
                                        />
                                    </div>
                                    <div class="form-group mb-3">
                                        <label
                                            for="email"
                                            class="form-label"
                                            >Email Address</label
                                        >
                                        <input
                                            type="email"
                                            class="form-control"
                                            id="email"
                                            name="email"
                                            placeholder="Your Email"
                                            required
                                        />
                                    </div>
                                    <div class="form-group mb-3">
                                        <label
                                            for="phone"
                                            class="form-label"
                                            >Phone Number</label
                                        >
                                        <input
                                            type="tel"
                                            class="form-control"
                                            id="phone"
                                            name="phone"
                                            placeholder="Your Phone"
                                            required
                                        />
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                             <?php $today_date = date('Y-m-d'); // Get today's date in YYYY-MM-DD format ?>
                                    <div class="col-md-6">
                                        <label for="date" class="form-label">Date</label>
                                        <input
                                            type="date"
                                            class="form-control"
                                            id="date"
                                            name="date"
                                            required
                                            min="<?php echo $today_date; ?>" />
                                    </div>
                                        </div>
                                        <div class="col-md-6">
                                             <label
                                                 for="time"
                                                 class="form-label"
                                                 >Time</label
                                             >
                                             <select
                                                 class="form-control"
                                                 id="time"
                                                 name="time"
                                                 required
                                             >
                                                  <option
                                                      value=""
                                                      disabled
                                                      selected
                                                  >
                                                       Select Time
                                                   </option>
                                                  <option value="17:00">5:00 PM</option>
                                                  <option value="17:30">5:30 PM</option>
                                                  <option value="18:00">6:00 PM</option>
                                                  <option value="18:30">6:30 PM</option>
                                                  <option value="19:00">7:00 PM</option>
                                                  <option value="19:30">7:30 PM</option>
                                                  <option value="20:00">8:00 PM</option>
                                                  <option value="20:30">8:30 PM</option>
                                                  <option value="21:00">9:00 PM</option>
                                              </select>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                         <label
                                             for="guests"
                                             class="form-label"
                                             >Number of Guests</label
                                         >
                                         <select
                                             class="form-control"
                                             id="guests"
                                             name="guests"
                                             required
                                         >
                                              <option
                                                  value=""
                                                  disabled
                                                  selected
                                              >
                                                   Select Guests
                                               </option>
                                               <option value="1">1 Person</option>
                                               <option value="2">2 People</option>
                                               <option value="3">3 People</option>
                                               <option value="4">4 People</option>
                                               <option value="5">5 People</option>
                                               <option value="6">6 People</option>
                                               <option value="7">7 People</option>
                                               <option value="8">8 People</option>
                                               <option value="9">9+ People</option>
                                           </select>
                                    </div>
                                    <input type="hidden" id="selectedTableId" name="table_id" value="">
                                     <input type="hidden" id="selectedTableType" name="table_type" value="">
                                     <input type="hidden" id="selectedTableLocation" name="table_location" value="">
                                     <input type="hidden" id="selectedTablePreference" name="table_preference" value="">


                                    <div class="form-group mb-4">
                                        <label
                                            for="message"
                                            class="form-label"
                                            >Special Requests
                                            (Optional)</label
                                        >
                                        <textarea
                                            class="form-control"
                                            id="special"
                                            name="special"
                                            rows="3"
                                            placeholder="Any special requests or dietary requirements?"
                                        ></textarea>
                                    </div>
                                    <div class="d-grid">
                                        <button
                                            type="submit"
                                            class="btn btn-primary btn-lg"
                                        >
                                             Confirm Reservation
                                         </button>
                                    </div>
                                     <div id="reservationMessage" class="mt-3"></div>

                                </form>
                            </div>
                            <div class="col-md-6 bg-light text-dark reservation-info">
                                 <div class="p-5">
                                    <h3 class="mb-4">Available Tables</h3>
                                     <div id="tableMapVisualization" style="min-height: 200px; border: 1px solid #ccc; padding: 10px; position: relative; background-color: #e9ecef; border-radius: 5px; overflow: auto;">
                                         <p class="text-muted">Select Date, Time, and Guests to see available tables.</p>
                                     </div>
                                     <small class="text-muted mt-2 d-block">Select an available table from the map above.</small>
                                     <div class="selected-table-info mt-3" id="selectedTableInfo" style="font-weight: bold;"></div>
                                 </div>
                                 <div class="p-5 mt-auto">
                                    <h3 class="mb-4">
                                        Reservation Details
                                    </h3>
                                    <div class="mb-4">
                                        <h5>Hours of Operation</h5>
                                        <ul class="list-unstyled">
                                            <li
                                                class="d-flex justify-content-between mb-2"
                                            >
                                                <span>Monday -
                                                    Thursday</span
                                                >
                                                <span>5:00 PM - 10:00
                                                    PM</span
                                                >
                                            </li>
                                            <li
                                                class="d-flex justify-content-between mb-2"
                                            >
                                                <span>Friday -
                                                    Saturday</span
                                                >
                                                <span>5:00 PM - 11:00
                                                    PM</span
                                                >
                                            </li>
                                            <li
                                                class="d-flex justify-content-between mb-2"
                                            >
                                                <span>Sunday</span>
                                                <span>5:00 PM - 9:00
                                                    PM</span
                                                >
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="mb-4">
                                        <h5>Reservation Policy</h5>
                                        <ul class="small">
                                            <li>
                                                Reservations are held
                                                for 15 minutes past the
                                                reserved time
                                            </li>
                                            <li>
                                                For parties of 9 or
                                                more, please call us
                                                directly
                                            </li>
                                            <li>
                                                Special requests are
                                                accommodated based on
                                                availability
                                            </li>
                                            <li>
                                                Cancellations: Please
                                                notify us 24 hours in
                                                advance
                                            </li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h5>Contact Information</h5>
                                        <p class="small mb-2">
                                            For immediate assistance or
                                            large party reservations:
                                        </p>
                                        <p class="mb-2">
                                            <i
                                                class="fas fa-phone-alt me-2"
                                            ></i>
                                            (555) 123-4567
                                        </p>
                                        <p>
                                            <i
                                                class="fas fa-envelope me-2"
                                            ></i>
                                            reservations@savoryhaven.com
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>