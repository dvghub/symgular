import { Component, OnInit } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Config} from '../config';

@Component({
  selector: 'app-welcome',
  templateUrl: './welcome.component.html',
  styleUrls: ['./welcome.component.css']
})
export class WelcomeComponent implements OnInit {
  config = new Config();
  passwordRepeatError = '';
  success = false;
  href;
  id;

  constructor(private http: HttpClient) {
    this.href = window.location.href.split('/');
    this.id = this.href[4];

    this.http.get(this.config.url + 'users/' + this.id + '/password').subscribe( data => {
      if ((data as any).isset) {
        window.location.href = '/login';
      }
    });
  }

  ngOnInit() {}

  submit(password, passwordRepeat) {
    this.passwordRepeatError = '';
    this.success = false;
    if (password === passwordRepeat) {
      this.http.patch(this.config.url + 'users/' + this.id + '/password', {password}).subscribe( data => {
        console.log(data);
        if ((data as any).success) {
          this.success = true;
          window.location.href = '/login';
        }
      });
    } else {
      this.passwordRepeatError = 'Passwords do not match.';
    }
  }
}
