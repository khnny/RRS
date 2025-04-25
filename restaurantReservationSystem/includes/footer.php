<!-- Footer -->
<footer class="bg-dark text-white py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <h3 class="h5 mb-3">Savory Haven</h3>
                        <p class="mb-4">
                            Experience culinary excellence in the heart of
                            downtown. Our passion is creating memorable dining
                            experiences with the finest ingredients.
                        </p>
                        <div class="social-icons mb-4">
                            <a href="#" class="me-2"
                                ><i class="fab fa-facebook-f"></i
                            ></a>
                            <a href="#" class="me-2"
                                ><i class="fab fa-twitter"></i
                            ></a>
                            <a href="#" class="me-2"
                                ><i class="fab fa-instagram"></i
                            ></a>
                            <a href="#"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2 mb-4 mb-md-0">
                        <h3 class="h5 mb-3">Quick Links</h3>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="#home" class="text-white-50">Home</a>
                            </li>
                            <li class="mb-2">
                                <a href="#about" class="text-white-50">About</a>
                            </li>
                            <li class="mb-2">
                                <a href="#menu" class="text-white-50">Menu</a>
                            </li>
                            <li class="mb-2">
                                <a href="#reservations" class="text-white-50"
                                    >Reservations</a
                                >
                            </li>
                            <li>
                                <a href="#contact" class="text-white-50"
                                    >Contact</a
                                >
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 col-lg-3 mb-4 mb-md-0">
                        <h3 class="h5 mb-3">Hours</h3>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                Monday - Thursday<br /><span
                                    class="text-white-50"
                                    >5:00 PM - 10:00 PM</span
                                >
                            </li>
                            <li class="mb-2">
                                Friday - Saturday<br /><span
                                    class="text-white-50"
                                    >5:00 PM - 11:00 PM</span
                                >
                            </li>
                            <li>
                                Sunday<br /><span class="text-white-50"
                                    >5:00 PM - 9:00 PM</span
                                >
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-3">
                        <h3 class="h5 mb-3">Newsletter</h3>
                        <p class="text-white-50">
                            Subscribe to receive updates on special events, new
                            menu items and exclusive offers.
                        </p>
                        <form class="mb-3">
                            <div class="input-group">
                                <input
                                    type="email"
                                    class="form-control"
                                    placeholder="Your email address"
                                    aria-label="Email address"
                                />
                                <button class="btn btn-primary" type="submit">
                                    Subscribe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <hr class="my-4 bg-light opacity-25" />
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0 text-white-50">
                            &copy; 2025 Savory Haven Restaurant. All rights
                            reserved.
                        </p>
                    </div>
                    <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item">
                                <a href="#" class="text-white-50"
                                    >Privacy Policy</a
                                >
                            </li>
                            <li class="list-inline-item">|</li>
                            <li class="list-inline-item">
                                <a href="#" class="text-white-50"
                                    >Terms of Service</a
                                >
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Reservation Confirmation Modal -->
        <div
            class="modal fade"
            id="reservationModal"
            tabindex="-1"
            aria-labelledby="reservationModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="modal-body text-center pb-5">
                        <div class="confirmation-icon mb-4">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <h3 class="mb-3" id="reservationModalLabel">
                            Reservation Confirmed!
                        </h3>
                        <p class="mb-4">
                            Thank you for your reservation. We've sent a
                            confirmation to your email.
                        </p>
                        <div
                            class="reservation-details bg-light p-3 rounded mb-4"
                        >
                            <div class="row mb-2">
                                <div class="col-6 text-start">Date:</div>
                                <div class="col-6 text-end" id="confirmDate">
                                    -
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-start">Time:</div>
                                <div class="col-6 text-end" id="confirmTime">
                                    -
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-start">Party Size:</div>
                                <div class="col-6 text-end" id="confirmGuests">
                                    -
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 text-start">
                                    Confirmation #:
                                </div>
                                <div class="col-6 text-end" id="confirmNumber">
                                    -
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center">
                            <button
                                type="button"
                                class="btn btn-outline-primary me-2"
                                data-bs-dismiss="modal"
                            >
                                Close
                            </button>
                            <button
                                type="button"
                                class="btn btn-primary"
                                id="addToCalendar"
                            >
                                Add to Calendar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS with Popper -->
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <!-- Custom JS -->
        <script src="assets/js/main.js"></script>
    </body>
</html>
