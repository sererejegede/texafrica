var app = new Vue({
  el: '#app',
  data: {
    registerForm: {
      first_name: '',
      last_name: '',
      phone_number: '',
      email: '',
      company_name: '',
      location: '',
      medium: '',
      occupation: ''
    },
    defaultForm: {
      first_name: '',
      last_name: '',
      phone_number: '',
      email: '',
      company_name: '',
      location: '',
      medium: '',
      occupation: ''
    },
    deadline: new Date("Jan 28, 2020 00:00:00").getTime(),
    now: '',
    difference: 0,
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0,
    interval: '',
    overlay: false,
    error_container: $('#register-error-container'),
    new_user: true,
    pal: false,
    registering: false,
    palDetails: {
      pal_first_name: '',
      pal_last_name: '',
      pal_email: '',
      pal_phone: '',
      pal_company_name: '',
      pal_location: '',
      pal_medium: '',
      pal_occupation: ''
    }
  },
  mounted() {
    // initialize counter
    if (localStorage.getItem('to_open') === 'yes') {
      this.toggleOverlay('open');
      localStorage.removeItem('to_open');
    }
    const cd = document.getElementById('countdown');
    if (cd) {
      document.getElementById('countdown').style.visibility = 'visible';
    }
    this.counter();
    const that = this;
    this.interval = setInterval(function () {
      that.counter();
      if (this.difference < 0) {
        clearInterval(this.interval)
      }
    }, 1000)
  },
  methods: {
    counter() {
      this.now = new Date().getTime();
      this.difference = this.deadline - this.now;
      this.days = Math.floor(this.difference / (1000 * 60 * 60 * 24));
      this.hours = Math.floor((this.difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      this.minutes = Math.floor((this.difference % (1000 * 60 * 60)) / (1000 * 60));
      this.seconds = Math.floor((this.difference % (1000 * 60)) / 1000);
      // Add leading zeros to day, hour, minute or second with 1 digit
      this.prettify();
    },
    showPal() {
      this.pal = !this.pal;
      $('#pal').toggleClass('fadeInRight');
    },
    newUser() {
      this.new_user = !this.new_user;
      document.getElementById('v-email__').scrollIntoView({behavior: 'smooth'});
    },
    prettify() {
      this.days = this.days.toString().length > 1 ? this.days : `0${this.days}`;
      this.hours = this.hours.toString().length > 1 ? this.hours : `0${this.hours}`;
      this.minutes = this.minutes.toString().length > 1 ? this.minutes : `0${this.minutes}`;
      this.seconds = this.seconds.toString().length > 1 ? this.seconds : `0${this.seconds}`;
    },
    titleCase(str) {
      str = str.toLowerCase().split(' ');
      for (var i = 0; i < str.length; i++) {
        str[i] = str[i].charAt(0).toUpperCase() + str[i].slice(1); 
      }
      return str.join(' ');
    },
    removeUnderScore(word) {
      return word.split('_').join(' ');
    },
    validateEmail(email) {
      const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(email);
    },
    toggleOverlay(action) {
      // this.overlay = action;
      $('#register-error-container').html('');
      document.getElementById('overlay').style.visibility = (action === 'open') ? 'visible' : 'hidden';
      this.new_user = true;
      this.registerForm.email = '';
      this.registerForm.company_name = '';
      this.registerForm.first_name = '';
      this.registerForm.last_name = '';
      this.registerForm.location = '';
      this.registerForm.medium = '';
      this.registerForm.occupation = '';
      this.registerForm.phone_number = '';
      this.palDetails.pal_first_name = '';
      this.palDetails.pal_last_name = '';
      this.palDetails.pal_email = '';
      this.palDetails.pal_phone = '';
      this.palDetails.pal_company_name = '';
      this.palDetails.pal_location = '';
      this.palDetails.pal_medium = '';
      this.palDetails.pal_occupation = '';
      this.pal = false;
    },
    redirect() {
      localStorage.setItem('to_open', 'yes');
      window.location.href = window.location.href.replace('gallery.html', 'index.html');
    },
    register(early_bird = false) {
      // console.log(this.registerForm);
      this.registering = true;
      const $error = $('#register-error-container');
      $error.removeClass('shake');
      const that = this;
      let form_errors = '';
      if (this.new_user) {
        for (const form_control in this.registerForm) {
          if (this.registerForm.hasOwnProperty(form_control) && !this.registerForm[form_control]) {
            form_errors += `${this.titleCase(this.removeUnderScore(form_control))} is required<br>`;
          }
        }
      } else {
        if (!this.registerForm.email) {
          form_errors += 'Email is required';
        } else if (!this.validateEmail(this.registerForm.email)) {
          form_errors += 'Email is invalid';
        }
      }

      if (form_errors.length) {
        document.getElementById('top-form').scrollIntoView({behavior: 'smooth'});
        $error.html(form_errors).addClass('shake');
        this.registering = false;
        return $error.addClass('shake');
      }
      let payload = {
         ...this.registerForm,
         returning: !this.new_user
      };
      if (this.pal) {
        payload = {...payload, ...this.palDetails}
      }
      $.post('server_files/register.php', payload, function (res) {
        const response = JSON.parse(res);
        if (response.status === 200) {
          // that.registerForm = that.defaultForm;
          that.pay(response.email, early_bird);
        } else if (response.status === 404 || response.status === 422 || response.status === 500) {
          $error.html(response.message);
          document.getElementById('top-form').scrollIntoView({behavior: 'smooth'});
          that.registering = false;
        }
      });
    },
    pay(email, early_bird) {
      const $error = $('#register-error-container');
      $error.removeClass('shake');
      $error.html('');
      this.payWithRave(email, early_bird);
    },
    payWithPaystack(email, early_bird) {
      const that = this;
      const is_pal = this.pal;
      const handler = PaystackPop.setup({
        key: 'pk_live_0329d8615b0915acf84dbc4b438f0eeb5ee32776', // pk_test_f3f33d97474d0eaeed17bb18fb694d70dc941f47
        email: email,
        amount: is_pal ? 160000 * 100 : early_bird ? 90000 * 100 : 100000 * 100, // NGN100,000
        currency: "NGN",
        callback: function (payment_res) {
          const nested_this = that;
          $.post('server_files/pay.php', {...payment_res, ...{email: email}}, function (res) {
            // console.log(res);
            if (res === 'Transaction Saved') {
              nested_this.verifyTransaction(payment_res.reference, email);
              $error.html('Transaction Saved');
            }
          })
        },
        onClose: function () {
          // that.toggleOverlay('close');
          document.getElementById('top-form').scrollIntoView({behavior: 'smooth'});
          $error.html('Could not complete transaction<br> Please retry');
          $error.addClass('shake');
          that.registering = false;
          /*Swal.fire({
            type: 'warning',
            title: '',
            text: 'Could not complete transaction',
            timer: 4000
          })*/
        }
      });
      handler.openIframe();
    },
    generateRef(email, phone) {
      return `${Math.floor(Math.random()*10000)}qP${email.split('@')[0]}TXF${email.split('@')[1]}`
    },
    payWithRave(email, early_bird) {
      const that = this;
      const is_pal = this.pal;
      const $error = $('#register-error-container');
      var handler = getpaidSetup({
        PBFPubKey: 'FLWPUBK_TEST-20a3ee86426d79ab4e2291d77ca3a1d6-X', // FLWPUBK-606347dc882e3339b9ee0b9b469633ee-X
        customer_email: email,
        currency: "NGN",
        customer_phone: that.registerForm.phone_number,
        amount: is_pal ? 150000 : early_bird ? 80000 : 90000,
        txref: that.generateRef(email, that.registerForm.phone_number),
        callback: function (payment_res) {
          // console.log("This is the payment_res returned after a charge", payment_res);
          const nested_this = that;
          if (payment_res.tx.chargeResponseCode === "00" || payment_res.tx.chargeResponseCode === "0") {
            $.post('server_files/pay.php', {...payment_res, email}, function (res) {
              // console.log(res);
              if (res === 'Transaction Saved') {
                nested_this.verifyTransaction(payment_res.tx.txRef, email);
                handler.close();
                $error.html('<span class="text-success">Transaction Saved</span>');
              }
            });
            // handler.close();
          } else {

          }
        },
        onclose: function () {
          // that.toggleOverlay('close');
          document.getElementById('top-form').scrollIntoView({behavior: 'smooth'});
          $error.html('Could not complete transaction<br> Please retry');
          $error.addClass('shake');
          that.registering = false;
          /*Swal.fire({
           type: 'warning',
           title: '',
           text: 'Could not complete transaction',
           timer: 4000
           })*/
        }
      });
    },
    verifyTransaction(reference, email) {
      const $error = $('#register-error-container');
      $error.removeClass('shake');
      $error.html('');
      const that = this;
      const is_pal = this.pal ? 1 : 0;
      $.post('server_files/verify.php', {reference, email, is_pal}, function (res) {
        console.log(res);
        that.registering = false;
        if (JSON.parse(res).verified) {
          Swal.fire({
            type: 'success',
            title: 'Success',
            text: 'Your registration has been completed successfully!',
            // timer: 4000
          });
          that.toggleOverlay('close');
          $error.html('Payment Successful');
        }
      })
    },
    postContactForm() {
      const $form = $('#contact-form'),
         $error = $form.find('.error-container'),
         action = $form.attr('action');
      $error.slideUp(750, function () {
        $error.hide();
        const $name = $form.find('.form-control-name'),
           $email = $form.find('.form-control-email'),
           $subject = $form.find('.form-control-subject'),
           $message = $form.find('.form-control-message');

        $.post(action, {
             name: $name.val(),
             email: $email.val(),
             subject: $subject.val(),
             message: $message.val()
           },
           function (data) {
             $error.html(data);
             $error.slideDown('slow');

             if (data === 'Your mail has been sent successfully.') {
               $name.val('');
               $email.val('');
               $subject.val('');
               $message.val('');
               Swal.fire({
                 type: 'success',
                 title: 'Thanks',
                 text: 'Thank you for contacting us. We\'ll contact you soon',
                 timer: 4000
               });
             }
           }
        );

      });
    },
    test(e) {
      console.log(PaystackPop);
    }
  },
  computed: {},
  watch: {}
});
