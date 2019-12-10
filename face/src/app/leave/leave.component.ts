import {Component, OnInit} from '@angular/core';
import {CookieService} from 'ngx-cookie-service';
import {HttpClient} from '@angular/common/http';

@Component({
  selector: 'app-leave',
  templateUrl: './leave.component.html',
  styleUrls: ['./leave.component.css']
})
export class LeaveComponent implements OnInit {
  user;
  today = new Date();
  date;
  days;
  year;
  success = false;
  description = '';
  startTimeError = '';
  endTimeError = '';
  descriptionError = '';

  month = {
      number: 0,
      name: ''
  };
  hours = {
      total: 200,
      left: 0
  };

  constructor(private cookieService: CookieService, private http: HttpClient) {
    if (!cookieService.check('session')) {
        window.location.href = '/';
    }

    let m = (this.today.getMonth() + 1).toString();
    if (m.length < 2) {
        m = '0' + m;
    }
    let d = this.today.getDate().toString();
    if (d.length < 2) {
        d = '0' + d;
    }
    this.date = this.today.getFullYear() + '-' + m + '-' + (d);

    this.month.number = this.today.getMonth() + 1;
    this.year = this.today.getFullYear();

    this.setMonth(this.month.number);

    if (this.cookieService.check('session')) {
        this.user = JSON.parse(cookieService.get('user'));
    }

    http.post('http://localhost:8000/hours', {email: this.user.email}).pipe().subscribe(data => {
        this.hours.left = (data as any).hours;
    });
  }

  static isLeapYear(year)  {
      return ((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0);
  }

  ngOnInit() {}

  request(startDate, startTime, endDate, endTime, type, description) {
    this.success = false;
    this.startTimeError = '';
    this.endTimeError = '';
    this.descriptionError = '';
    this.description = '';

    this.http.post('http://localhost:8000/request', {
      start_date: startDate,
      start_time: startTime,
      end_date: endDate,
      end_time: endTime,
      type,
      description,
      email: this.user.email
    }).pipe().subscribe( data => {
      console.log(JSON.stringify(data));
      if (!(data as any).success) {
        this.startTimeError = (data as any).start_time_error;
        this.endTimeError = (data as any).end_time_error;
        this.descriptionError = (data as any).description_error;
      } else {
          this.success = (data as any).success;
          this.hours.left = (data as any).hours;
      }
    });
  }

  setMonth(n) {
    this.month.number = n;
    switch (n) {
      case 1:
        this.days = new Array(31);
        this.month.name = 'January';
        break;
      case 2:
        this.days = LeaveComponent.isLeapYear(this.year) ? new Array(29) : new Array(28);
        this.month.name = 'February';
        break;
      case 3:
        this.days = new Array(31);
        this.month.name = 'March';
        break;
      case 4:
        this.days = new Array(30);
        this.month.name = 'April';
        break;
      case 5:
        this.days = new Array(31);
        this.month.name = 'May';
        break;
      case 6:
        this.days = new Array(30);
        this.month.name = 'June';
        break;
      case 7:
        this.days = new Array(31);
        this.month.name = 'July';
        break;
      case 8:
        this.days = new Array(31);
        this.month.name = 'August';
        break;
      case 9:
        this.days = new Array(30);
        this.month.name = 'September';
        break;
      case 10:
        this.days = new Array(31);
        this.month.name = 'October';
        break;
      case 11:
        this.days = new Array(30);
        this.month.name = 'November';
        break;
      case 12:
        this.days = new Array(31);
        this.month.name = 'December';
        break;
    }

    this.http.post('http://localhost:8000/requests', {}).pipe().subscribe( data => {
    });

    let i;
    for (i = 0; i < this.days.length; i ++) {
      this.days[i] = new Date(this.year, this.month.number - 1, i + 1);
    }
  }

  previousMonth() {
    if (this.month.number === 1) {
      this.month.number = 12;
      this.year -= 1;
    } else {
      this.month.number -= 1;
    }
    this.setMonth(this.month.number);
  }

  nextMonth() {
    if (this.month.number === 12) {
      this.month.number = 1;
      this.year += 1;
    } else {
      this.month.number += 1;
    }
    this.setMonth(this.month.number);
  }
}
