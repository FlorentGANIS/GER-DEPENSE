import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';

import { environment } from '../../../environments/environment';
import { AuthenticationService } from '../../core/services/auth.service';
import { UserProfileService } from '../../core/services/user.service';
import { LAYOUT_MODE } from '../../layouts/layouts.model';
import { Router } from '@angular/router';
import { first } from 'rxjs/operators';
import { PasswordValidator } from 'src/app/validators/password.validator';
import { AuthService } from 'src/app/shared/authentication/auth.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.scss']
})

/**
 * Register Component
 */
export class RegisterComponent implements OnInit {

  // set the currenr year
  year: number = new Date().getFullYear(); 

  register_form!: FormGroup; is_processing: boolean = false; message: string = '';

  // Carousel navigation arrow show
  showNavigationArrows: any;

  layout_mode!: string;

  signupForm!: FormGroup;
  submitted = false;
  successmsg = false;
  error = '';
  email_sent: boolean = false;
  display_confirmation: boolean = false;
  email_valid: boolean = false;
  email_temp: any;

  constructor(private formBuilder: FormBuilder, private auth_service: AuthService,
    private router: Router, private toastr: ToastrService,
    //private authenticationService: AuthenticationService, private userService: UserProfileService
    ) { }

  ngOnInit(): void {
    this.layout_mode = LAYOUT_MODE
    if (this.layout_mode === 'dark') {
      document.body.setAttribute("data-bs-theme", "dark");
    }

    this.register_form = this.formBuilder.group({
      last_name: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(30)]],
      first_name: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(50)]],
      email: ['', [Validators.required, Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$")]],
      password: ['', [Validators.required]],
      password_confirmation: ['', [Validators.required]],
      code_verification: ['', [Validators.required]],
    },
      {
        validator: [PasswordValidator('password', 'password_confirmation'),],
      });

    // Validation Set
    // this.signupForm = this.formBuilder.group({
    //   username: ['', Validators.required],
    //   email: ['', [Validators.required, Validators.email]],
    //   password: ['', Validators.required],
    // });
    document.body.setAttribute('data-layout', 'vertical');
  }

  // convenience getter for easy access to form fields
  get f() { return this.signupForm.controls; }

  /**
   * On submit form
   */
  // onSubmit() {
  //   this.submitted = true;
  //   // stop here if form is invalid
  //   if (this.signupForm.invalid) {
  //     return;
  //   } else {
  //     if (environment.defaultauth === 'firebase') {
  //       this.authenticationService.register(this.f.email.value, this.f.password.value).then((res: any) => {
  //         this.successmsg = true;
  //         if (this.successmsg) {
  //           this.router.navigate(['']);
  //         }
  //       })
  //         .catch((error: string) => {
  //           this.error = error ? error : '';
  //         });
  //     } else {
  //       this.userService.register(this.signupForm.value)
  //         .pipe(first())
  //         .subscribe(
  //           (data: any) => {
  //             this.successmsg = true;
  //             if (this.successmsg) {
  //               this.router.navigate(['/account/login']);
  //             }
  //           },
  //           (error: any) => {
  //             this.error = error ? error : '';
  //           });
  //     }
  //   }
  // }

  sendEmailVerificationCode() {
    this.is_processing = true;
    this.auth_service.getConfirmationCode({ email: this.register_form.value.email }).subscribe(
      {
        next: (v: any) => {
          this.message = v.message;
          if (v.status == 200) {
            this.email_sent = true;
            this.display_confirmation = true;
            this.is_processing = false;
            this.showSuccessViaToast(this.message);
          } else {
            this.email_sent = false;
            this.is_processing = false;
            this.showErrorViaToast(this.message);
          }
        },
        error: (e) => {
          console.error(e);
          this.email_sent = false;
          this.is_processing = false;
          this.showErrorViaToast(this.message);
        }
      });
  }

  verifyCode() {
    this.is_processing = true;

    let code = this.register_form.get('code_verification')?.value;

    this.register_form.controls['code_verification'].disable();

    this.auth_service.verificationCode({
      'email': this.register_form.get('email')?.value,
      'code': code
    }).subscribe({
      next: (v: any) => {
        this.message = v.message;
        if (v.status == 200) {
          this.is_processing = false;
          this.showSuccessViaToast(this.message);
          this.email_valid = true;
          this.display_confirmation = false;
          this.email_temp = this.register_form.get('email')?.value;
        } else {
          this.register_form.patchValue({ code_verification: '' });
          this.register_form.controls['code_verification'].enable();
          this.is_processing = false;
          this.showErrorViaToast(this.message)
        }
      },

      error: (e) => {
        console.error(e);
        this.register_form.patchValue({ code_verification: '' });
        this.register_form.controls['code_verification'].enable();
        this.is_processing = false;
        this.showErrorViaToast(this.message)
      },

      complete: () => {

      }
    });

  }

  resendEmailVerificationCode() {
    this.is_processing = true;

    this.auth_service.getNewConfirmationCode({ email: this.email_temp }).subscribe({
      next: (v: any) => {
        this.message = v.message;
        if (v.status == 200) {
          this.is_processing = false;
          this.showSuccessViaToast(this.message);
        } else {
          this.is_processing = false;
          this.showErrorViaToast(this.message);
        }
      },

      error: (e) => {
        console.error(e);
        this.showErrorViaToast(this.message);
      },

      complete: () => {

      }
    });
  }

  register() {
    this.is_processing = true;
    this.register_form.patchValue({email: this.email_temp});
    this.auth_service.register(this.register_form.value).subscribe({
      next: (v: any) => {
        if (v.status == 200) {
          this.message = v.message;
          this.showSuccessViaToast(this.message);
          this.is_processing = false;
          setTimeout(() => {
            this.navigateToLogin();
          }, 700);
          this.register_form.reset();
        } else {
          this.message = v.message;
          this.showErrorViaToast(this.message);
          this.is_processing = false;
        }

      },
      error: (error: any) => {
        let er = 'Impossible de valider vos identifiants. Veuillez réessayer.';
        this.showErrorViaToast(er);
        this.is_processing = false;
      },
      complete: () => {
      },
    })
  }

  showSuccessViaToast(msg: any) {
    this.toastr.success("Succès", msg)
  }

  showErrorViaToast(msg: any) {
    this.toastr.error("Echec", msg)
  }

  navigateToLogin() {
    this.router.navigate(['/login']);
  }

}
