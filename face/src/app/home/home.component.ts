import {Component, OnInit} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';
import {Config} from '../config';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css']
})
export class HomeComponent implements OnInit {
  employees;
  user;
  today = new Date();
  birthday;
  notices;
  config = new Config();

  constructor(private http: HttpClient, private cookieService: CookieService) {
    http.get(this.config.url + 'users/birthday').subscribe( data => {
      this.employees = (data as any).employees;
      let i;
      for (i = 0; i < this.employees.length; i++) {
        this.employees[i].birthday = new Date(this.employees[i].birthday).getDate();
      }
    });
    if (cookieService.check('user')) {
      this.user = JSON.parse(cookieService.get('user'));
      this.birthday = new Date(this.user.birthday);
    }
    this.getNotices();
  }

  ngOnInit() {}

  getNotices() {
    this.http.get(this.config.url + 'notices').subscribe( data => {
      this.notices = (data as any).notices.reverse();

      for (const notice of this.notices) {
        notice.message = notice.message.replace(/\\/g, '');
        const date = new Date(notice.timestamp);
        notice.timestamp =
          date.getDay() + '-' +
          date.getMonth() + '-' +
          date.getFullYear() + ' ' +
          date.getHours() + ':' +
          (date.getMinutes().toString().length === 1 ? '0' + date.getMinutes() : date.getMinutes());
      }
    });
  }

  deleteNotice(id) {
    this.http.delete(this.config.url + 'notices/' + id).subscribe( data => {
      if ((data as any).success) {
        this.getNotices();
      }
    });
  }
}
