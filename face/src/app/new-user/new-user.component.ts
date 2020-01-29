import { Component, OnInit } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';
import {Config} from '../config';
import {sendRequest} from 'selenium-webdriver/http';

@Component({
  selector: 'app-new-user',
  templateUrl: './new-user.component.html',
  styleUrls: ['./new-user.component.css']
})
export class NewUserComponent implements OnInit {
  config = new Config();
  success = false;
  sent = false;
  firstName = '';
  firstNameError = '';
  lastNameError = '';
  emailError = '';

  constructor(private http: HttpClient, private cookieService: CookieService) {
    if (!cookieService.check('user') &&
      !JSON.parse(this.cookieService.get('user')).admin &&
      JSON.parse(this.cookieService.get('user')).department !== 'hr') {
      window.location.href = '/';
    }
  }

  ngOnInit() {}

  register(firstName, lastName, email, department, birthday, admin) {
    this.success = false;
    this.sent = false;
    this.firstNameError = '';
    this.lastNameError = '';
    this.emailError = '';

    this.http.post(this.config.url + 'user', {
      first_name: firstName,
      last_name: lastName,
      email,
      department,
      birthday,
      admin
    }).subscribe( data => {
      if ((data as any).success) {
        this.success = true;
        this.firstName = firstName;
        (document.getElementById('first_name') as any).value = '';
        (document.getElementById('last_name') as any).value = '';
        (document.getElementById('email') as any).value = '@symgular.com';
        (document.getElementById('birthday') as any).value = '1000-01-01';

        this.http.post(this.config.url + 'mailer/welcome/' + (data as any).id, {}).subscribe( (d) => {
          if ((d as any).success) {
            this.sent = true;
          }
        });
      } else {
        this.firstNameError = (data as any).firstNameError;
        this.lastNameError = (data as any).lastNameError;
        this.emailError = (data as any).emailError;
      }
    });
  }
}
