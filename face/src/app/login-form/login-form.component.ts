import {Component, OnInit} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';
import {Config} from '../config';

@Component({
  selector: 'app-login-form',
  templateUrl: './login-form.component.html',
  styleUrls: ['./login-form.component.css']
})

export class LoginFormComponent implements OnInit {
  config = new Config();
  user;

  emailError;
  passwordError;

  constructor(private http: HttpClient, private cookieService: CookieService) {
    if (cookieService.check('session')) {
      window.location.href = '/';
    }
  }

  ngOnInit() {}

  login(email, password) {
      this.http.post(this.config.url + 'session/users', {email, password}).pipe().subscribe(data => {
          console.log(data);
          if ((data as any).success) {
              this.cookieService.set('session', (data as any).sessionId);
              this.user = (data as any).user;
              this.user.email = email;
              this.cookieService.set('user', JSON.stringify(this.user));
              window.location.href = '/';
          } else {
              this.emailError = (data as any).emailError;
              this.passwordError = (data as any).passwordError;
          }
      });
  }
}
