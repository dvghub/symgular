import {Component, OnInit} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';

@Component({
  selector: 'app-login-form',
  templateUrl: './login-form.component.html',
  styleUrls: ['./login-form.component.css']
})

export class LoginFormComponent implements OnInit {
  user = {
      firstName: '',
      lastName: '',
      email: '',
      department: '',
      birthday: '',
      admin: false
  };

  emailError;
  passwordError;

  constructor(private http: HttpClient, private cookieService: CookieService) {
    if (cookieService.check('session')) {
      window.location.href = '/';
    }
  }

  ngOnInit() {}

  login(email, password) {
      this.http.post('http://localhost:8000/login', {email, password}).pipe().subscribe(data => {
          if ((data as any).success) {
              this.cookieService.set('session', (data as any).session_id);
              this.user.firstName = (data as any).first_name;
              this.user.lastName = (data as any).last_name;
              this.user.email = email;
              this.user.department = (data as any).department;
              this.user.birthday = (data as any).birthday;
              this.user.admin = (data as any).admin === 1;
              this.cookieService.set('user', JSON.stringify(this.user));
              window.location.href = '/';
          } else {
              this.emailError = (data as any).email_error;
              this.passwordError = (data as any).password_error;
          }
      });
  }
}
