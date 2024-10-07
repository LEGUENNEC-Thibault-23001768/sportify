DROP TABLE IF EXISTS TEAM_PARTICIPANT;
DROP TABLE IF EXISTS TEAM;
DROP TABLE IF EXISTS EVENT_REGISTRATION;
DROP TABLE IF EXISTS EVENTS;
DROP TABLE IF EXISTS COURT_RESERVATION;
DROP TABLE IF EXISTS COURT;
DROP TABLE IF EXISTS PERFORMANCE;
DROP TABLE IF EXISTS RESERVATION_HISTORY;
DROP TABLE IF EXISTS SUBSCRIPTION;
DROP TABLE IF EXISTS MEMBER;

CREATE TABLE MEMBER (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(100),
    first_name VARCHAR(100),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    birth_date DATE,
    address TEXT,
    phone VARCHAR(15),
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE SUBSCRIPTION (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    subscription_type VARCHAR(50),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_member_subscription
        FOREIGN KEY (member_id) REFERENCES MEMBER(member_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE RESERVATION_HISTORY (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    activity VARCHAR(100), 
    reservation_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    CONSTRAINT fk_member_reservation
        FOREIGN KEY (member_id) REFERENCES MEMBER(member_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE EVENTS (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100),
    event_date DATE,
    start_time TIME,
    end_time TIME,
    description TEXT,
    max_participants INT
);

CREATE TABLE EVENT_REGISTRATION (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    event_id INT,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_member_event_registration
        FOREIGN KEY (member_id) REFERENCES MEMBER(member_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_event_registration
        FOREIGN KEY (event_id) REFERENCES EVENTS(event_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE TEAM (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100),
    event_id INT,
    CONSTRAINT fk_event_team
        FOREIGN KEY (event_id) REFERENCES EVENTS(event_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE TEAM_PARTICIPANT (
    participant_id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    member_id INT,
    CONSTRAINT fk_team_participant
        FOREIGN KEY (team_id) REFERENCES TEAM(team_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_member_participant
        FOREIGN KEY (member_id) REFERENCES MEMBER(member_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE COURT (
    court_id INT AUTO_INCREMENT PRIMARY KEY,
    court_name VARCHAR(100),
    activity_type VARCHAR(100), -- football, badminton, etc.
    max_capacity INT
);

CREATE TABLE COURT_RESERVATION (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    court_id INT,
    reservation_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    CONSTRAINT fk_member_court_reservation
        FOREIGN KEY (member_id) REFERENCES MEMBER(member_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_court_reservation
        FOREIGN KEY (court_id) REFERENCES COURT(court_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE PERFORMANCE (
    performance_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    activity VARCHAR(100), -- football, badminton, etc.
    score INT,
    play_time TIME,
    performance_date DATE,
    CONSTRAINT fk_member_performance
        FOREIGN KEY (member_id) REFERENCES MEMBER(member_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);
