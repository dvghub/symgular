import { Component, OnInit } from '@angular/core';
import {HttpClient} from '@angular/common/http';

@Component({
  selector: 'app-new-user',
  templateUrl: './new-user.component.html',
  styleUrls: ['./new-user.component.css']
})
export class NewUserComponent implements OnInit {

  constructor(private http: HttpClient) {}

  registered = false;
  registeredName = '';

  ngOnInit() {}

  register(firstName, lastName, email, department, birthday, admin) {
    console.log();
    this.http.post('http://localhost:8000/register', {
      first_name: firstName,
      last_name: lastName,
      email,
      department: department[0].value,
      birthday,
      admin
    }).pipe().subscribe( data => {
      this.registered = (data as any).registered;
      this.registeredName = (data as any).registered_name;
    });
  }
}
