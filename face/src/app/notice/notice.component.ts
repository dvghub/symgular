import { Component, OnInit } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';

@Component({
  selector: 'app-notice',
  templateUrl: './notice.component.html',
  styleUrls: ['./notice.component.css']
})
export class NoticeComponent implements OnInit {
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
    this.http.post('http://localhost:8000/notice', {title, message, email: this.user.email}).pipe().subscribe( data => {
      this.success = (data as any).success;
    });
  }
}
