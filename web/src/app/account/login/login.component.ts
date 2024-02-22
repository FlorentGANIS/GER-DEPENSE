import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { LAYOUT_MODE } from '../../layouts/layouts.model';
import { AuthService } from 'src/app/shared/authentication/auth.service';
import { AuthStateService } from 'src/app/shared/authentication/auth-state.service';
import { TokenService } from 'src/app/shared/authentication/token.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})

/**
 * Login Component
 */
export class LoginComponent implements OnInit {
  isProcessing: boolean = false; message: string = '';
  // set the currenr year
  year: number = new Date().getFullYear();
  // Carousel navigation arrow show
  showNavigationArrows: any;
  loginForm!: FormGroup;
  submitted = false;
  error = '';
  returnUrl!: string;
  layout_mode!: string;
  fieldTextType!: boolean;

  constructor(private formBuilder: FormBuilder,
    private route: ActivatedRoute,
    private router: Router, private authService: AuthService,
     private authState: AuthStateService, private tokenService: TokenService,
    //private authenticationService: AuthenticationService,
    //private authFackservice: AuthfakeauthenticationService,
  ) {
    // redirect to home if already logged in
    // if (this.authenticationService.currentUserValue) {
    //   this.router.navigate(['/']);
    // }
  }

  ngOnInit(): void {
    this.layout_mode = LAYOUT_MODE
    if (this.layout_mode === 'dark') {
      document.body.setAttribute("data-bs-theme", "dark");
    }

    this.loginForm = this.formBuilder.group({
      email: ['', [Validators.required, Validators.pattern("^[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$")]],
      password: ['', [Validators.required]],
    });
    //Validation Set
    // this.loginForm = this.formBuilder.group({
    //   email: ['admin@themesbrand.com', [Validators.required, Validators.email]],
    //   password: ['123456', [Validators.required]],
    // });
    // get return url from route parameters or default to '/'
    // this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/';
    // document.body.setAttribute('data-layout', 'vertical');
  }

  // convenience getter for easy access to form fields
  get f() { return this.loginForm.controls; }

  /**
   * Form submit
   */
  onSubmit() {
    if (this.loginForm.invalid) {
      
    } else {
      this.isProcessing = true;
      this.authService.login(this.loginForm.value).subscribe({
        next: (v: any) => {
          if (v.status == 200) {
            this.message = v.message;
            //this.showSuccess(this.message);
            this.tokenService.handleToken(v.access_token);
            this.authState.changeAuthStatus(true);  
            this.isProcessing = false;
            this.router.navigate(['dashboard']);          
            this.loginForm.reset();

          }else{
            this.message = v.message;
            this.loginForm.patchValue({
              password: ''
            });
            //this.showError(this.message);
            this.isProcessing = false;
          }      

        },
        error: (error: any) => {
          this.error = 'Impossible de valider vos identifiants. Veuillez réessayer.';
          this.isProcessing = false;
        },
        complete: () => {
        },
      })
    }
    // this.submitted = true;

    // // stop here if form is invalid
    // if (this.loginForm.invalid) {
    //   return;
    // } else {
    //   if (environment.defaultauth === 'firebase') {
    //     this.authenticationService.login(this.f.email.value, this.f.password.value).then((res: any) => {
    //       this.router.navigate(['/']);
    //     })
    //       .catch(error => {
    //         this.error = error ? error : '';
    //       });
    //   } else {
    //     this.authFackservice.login(this.f.email.value, this.f.password.value)
    //       .pipe(first())
    //       .subscribe(
    //         data => {
    //           this.router.navigate(['/']);
    //         },
    //         error => {
    //           this.error = error ? error : '';
    //         });
    //   }
    // }
  }

  /**
   * Password Hide/Show
   */
  toggleFieldTextType() {
    this.fieldTextType = !this.fieldTextType;
  }

}
