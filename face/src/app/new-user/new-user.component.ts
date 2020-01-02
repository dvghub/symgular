import { Component, OnInit } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';
import {Config} from '../config';

@Component({
  selector: 'app-new-user',
  templateUrl: './new-user.component.html',
  styleUrls: ['./new-user.component.css']
})
export class NewUserComponent implements OnInit {
  config = new Config();
  success = false;
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
    this.http.post(this.config.url + 'user', {
      first_name: firstName,
      last_name: lastName,
      email,
      department,
      birthday,
      admin
    }).pipe().subscribe( data => {
      if ((data as any).success) {
        this.success = true;
        this.firstName = firstName;
        (document.getElementById('first_name') as any).value = '';
        (document.getElementById('last_name') as any).value = '';
        (document.getElementById('email') as any).value = '@symgular.com';
        (document.getElementById('birthday') as any).value = '1000-01-01';
      } else {
        this.firstNameError = (data as any).firstNameError;
        this.lastNameError = (data as any).lastNameError;
        this.emailError = (data as any).emailError;
      }
    });
  }
}
