import { Component, OnInit } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';
import {Config} from '../config';

@Component({
  selector: 'app-notice',
  templateUrl: './notice.component.html',
  styleUrls: ['./notice.component.css']
})
export class NoticeComponent implements OnInit {
  config = new Config();
  user;
  success = false;

  constructor(private http: HttpClient, private cookieService: CookieService) {
    if (cookieService.check('user')) {
      this.user = JSON.parse(this.cookieService.get('user'));
      if (!this.user.admin && this.user.department !== 'hr') {
        window.location.href = '/';
      }
    } else {
      window.location.href = '/';
    }
  }

  ngOnInit() {}

  post(title, message) {
    this.success = false;
    this.http.post(this.config.url + 'notice', {title, message, email: this.user.email}).pipe().subscribe( data => {
      if ((data as any).success) {
        this.success = true;
      }
    });
  }
}
