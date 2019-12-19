import {Component, OnInit} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';
import {not} from 'rxjs/internal-compatibility';

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

  constructor(private http: HttpClient, private cookieService: CookieService) {
    http.get('http://localhost:8000/birthdays').subscribe( data => {
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
    this.http.get('http://localhost:8000/notices').subscribe( data => {
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

      console.log(this.notices);
    });
  }

  deleteNotice(id) {
    this.http.post('http://localhost:8000/deletenotice', {id}).pipe().subscribe( data => {
      if ((data as any).success) {
        this.getNotices();
      }
    });
  }
}
