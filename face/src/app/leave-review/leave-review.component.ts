import { Component, OnInit } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';

@Component({
  selector: 'app-leave-review',
  templateUrl: './leave-review.component.html',
  styleUrls: ['./leave-review.component.css']
})
export class LeaveReviewComponent implements OnInit {
  user;
  requests;
  selected;
  current;

  constructor(private http: HttpClient, private cookieService: CookieService) {
    if (cookieService.check('user')) {
      this.user = JSON.parse(this.cookieService.get('user'));
      if (!this.user.admin && this.user.department !== 'hr') {
        window.location.href = '/';
      }
    } else {
      window.location.href = '/';
    }

    http.get('http://localhost:8000/unapproved').subscribe( data => {
      this.requests = (data as any).requests;
      if (this.requests.length !== 0) {
        this.load(this.requests[0].id);
      }
    });
  }

  ngOnInit() {}

  load(id) {
    this.selected = id;
    this.requests.forEach( (item, index) => {
      if (this.requests[index].id === id) {
        this.current = this.requests[index];
        console.log(this.current);
      }
    });
  }

  approve(id) {
    console.log(id);
    this.http.post('http://localhost:8000/approve', {id}).pipe().subscribe( data => {
      console.log(data);
      if ((data as any).success) {
        this.requests.forEach( (item, index) => {
          if (this.requests[index].id === id) {
            this.requests.splice(index, 1);
          }
        });
        this.ngOnInit();
      }
    });
  }

  deny(id) {
    console.log(id);
    this.http.post('http://localhost:8000/deny', {id}).pipe().subscribe( data => {
      console.log(data);
      if ((data as any).success) {
        this.requests.forEach( (item, index) => {
          if (this.requests[index].id === id) {
            this.requests.splice(index, 1);
          }
        });
        this.ngOnInit();
      }
    });
  }
}
