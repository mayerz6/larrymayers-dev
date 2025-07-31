<?php
header('Content-Type: application/json');

$positions = [
    [
        "title"     =>  "AWS Data Engineer",
        "company"   =>  "Protexxa",
        "duration"  =>  "2023 - Present",
        "summary"   =>  "",
        "duties"    =>  [
            "ETL procedures for a cutting-edge cyber security platform.",
            "Juggled AWS services like S3, EC2, EMR, and RDS with flair.",
            "Architected data warehousing solutions using AWS Glue, Redshift, and friends.",
            "Troubleshooter extraordinaire, ensuring data pipeline integrity.",
            "Collaborated cross-functionally to design efficient data models.",
            "Explored and integrated Machine Learning models to enhance data analytics."
        ]
    ],
    [
        "title"     =>  "Network Administrator & QA Analyst",
        "company"   =>  "Candrug International Ltd.",
        "duration"  =>  "2019 - 2023",
        "summary"   =>  "",
        "duties"    =>  [
            "Masterminded ETL procedures for a cutting-edge cyber security platform.",
            "Juggled AWS services like S3, EC2, EMR, &amp; RDS with flair.",
            "Architected data warehousing solutions using AWS Glue, Redshift, and friends.",
            "Troubleshooter extraordinaire, ensuring data pipeline integrity.",
            "Collaborated cross-functionally to design efficient data models.",
            "Explored and integrated Machine Learning models to enhance data analytics."
        ]
    ],
    [
        "title"     =>  "Web Developer & Network Administrator",
        "company"   =>  "Candrug International Ltd.",
        "duration"  =>  "2017 - 2019",
        "summary"   =>  "",
        "duties"    =>  [
            "Propose wireframe illustrations for the web interfaces on a per project basis.",
            "Manages user account deployment along with technical support within an active directory environment.",
            "Actively administer and manage the network, workstation operating systems, security systems, safety backups and servers related to the systems within the company network.",
            "Assist and support in the upkeep and maintenance of websites as business requirements evolve.",
            "Troubleshooting network/internet errors and problems.",
            "Provide reports on monthly online engagement (via predefined KPI metrics) of ecommerce sites to give direction to business development objectives.",
            "Advise on the acquisitions of hardware and software as necessary to mitigate company infrastructure vulnerabilities.",
            "Maintain inventory documentation of technological company assets.",
            "Maintain bug tracking of any faults (system, network or web based application) and record the associated remediation steps & subsequent solution."
        ]
    ],
    [
        "title"     =>  "Information Technology Coordinator & Research Co-Supervisor",
        "company"   =>  "The Substance Abuse Foundation Inc.",
        "duration"  =>  "2015 - 2017",
        "summary"   =>  "",
        "duties"    =>  [
            "Coordination of Research Methodologies - Take responsibility for the planning, administration and reviewing of research activities necessary to investigate specified research hypotheses.",
            "Coordinate Research Interns – Management and assignment of tasks; for execution by team of research interns.",
            "Supervise Statistical Analysis – Accurately outline, monitor and examine trends during the evolution of the statistical dissemination process.",
            "Statistical Reporting – Present research findings and influence programme effectiveness to strengthen the Clinical team.",
            "Management of Social Media presence - Take responsibility for the day-to-day management of the online content to ensure a sustained audience via Facebook services.",
            "Management of the institution's official website, its content and their sustained presence.",
            "Manages user account deployment along with technical support within an active directory environment.",
            "Actively administer and manage the network, workstation operating systems, security systems, safety backups and servers related to the systems within the company network.",
            "Maintain inventory documentation of technological company assets."
            ]
    ],
    [
        "title"     =>  "Information Technology Coordinator",
        "company"   =>  "The Substance Abuse Foundation Inc.",
        "duration"  =>  "2013 - 2015",
        "summary"   =>  "",
        "duties"    =>  [
            "Plan, Setup and maintain all telephony systems within the office to facilitate LAN and WAN communications.",
            "Actively administer and manage the network, workstation, security systems, while maintaining successful backups of server records related to the systems within the company active directory network environment.",
            "Advise on peripheral devices throughout the network as well as compose new and update existing documentation to identify all technological assets, any previous faults and the executed solutions."
        ]
    ]
];


echo json_encode($positions);

