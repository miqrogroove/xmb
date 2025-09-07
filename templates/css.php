/* XMB Stylesheet */
body {
    scrollbar-arrow-color: <?= $THEME['header'] ?>;
    scrollbar-base-color: <?= $THEME['altbg1'] ?>;
    text-align: left;
    <?= $THEME['bgcode'], "\n" ?>
}

a {
    color: <?= $THEME['link'] ?>;
    text-decoration: none;
}

a:hover, .category a:hover {
    text-decoration: underline;
}

.category a {
    color: <?= $THEME['cattext'] ?>;
    text-decoration: none;
}

form {
    margin: 0px;
}

hr {
    color:  <?= $THEME['bordercolor'] ?>;
    background-color: <?= $THEME['bordercolor'] ?>;
    border: 0px;
    height: 1px;
}

#tickertoggle {
    color: <?= $THEME['cattext'] ?>;
}

table.code {
    border: 1px solid <?= $THEME['bordercolor'] ?>;
    margin: 15px auto 10px auto;
}

td.code {
    background-color: <?= $THEME['header'] ?>;
    border-bottom: 1px solid <?= $THEME['bordercolor'] ?>;
    color: <?= $THEME['headertext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    font-weight: bold;
    height: 20px;
    padding-left: 5px;
}

code {
    border: 1px solid <?= $THEME['bordercolor'] ?>;
    padding: 1px 4px;
}

.codemessage code {
    background-color: <?= $THEME['altbg2'] ?>;
    color: <?= $THEME['tabletext'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    padding: 10px 5px 10px 5px;
    white-space: pre;
    display: block;
    width: 600px;
    max-height: 400px;
    overflow: auto;
    border: none;
}

table.quote {
    border: 1px solid <?= $THEME['bordercolor'] ?>;
    margin: 15px 5% 10px 5%;
    width: 80%;
}

td.quote {
    background-color: <?= $THEME['header'] ?>;
    border-bottom: 1px solid <?= $THEME['bordercolor'] ?>;
    color: <?= $THEME['headertext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    font-weight: bold;
    height: 20px;
    padding-left: 5px;
}

td.quotemessage {
    background-color: <?= $THEME['altbg2'] ?>;
    color: <?= $THEME['tabletext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    padding: 10px 0 20px 5px;
}

textarea, select, input, object {
    background-color: <?= $THEME['altbg1'] ?>;
    border: 1px solid <?= $THEME['bordercolor'] ?>;
    color: <?= $THEME['tabletext'] ?>;
    font-family: Verdana, arial, helvetica, sans-serif;
    font-size: 12px;
    font-weight: normal;
    table-layout: fixed;
}

.category {
    <?= $THEME['catcss'] ?>
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    table-layout: fixed;
    text-align: left;
}

.ctrcategory {
    <?= $THEME['catcss'] ?>
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    table-layout: fixed;
    text-align: center;
}

.ctrtablerow {
    color: <?= $THEME['tabletext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    table-layout: fixed;
    text-align: center;
}

.header {
    background-color: <?= $THEME['header'] ?>;
    color: <?= $THEME['headertext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    font-weight: bold;
    table-layout: fixed;
}

.header2 {
    background-color: <?= $THEME['altbg1'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    font-weight: bold;
    table-layout: fixed;
}

.mediumtxt {
    color: <?= $THEME['tabletext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    font-weight: normal;
    table-layout: fixed;
}

.multi {
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    table-layout: fixed;
}

.nav {
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    font-weight: bold;
    table-layout: fixed;
}

.navtd {
    background-color: <?= $THEME['header'] ?>;
    color: <?= $THEME['headertext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    table-layout: fixed;
    text-decoration: none;
}

.navtd a {
    white-space: nowrap;
}

.navtd2 {
    background-color: <?= $THEME['header'] ?>;
    color: <?= $THEME['headertext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: 9px;
    table-layout: fixed;
    text-decoration: none;
}

.post {
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['font3'] ?>;
    font-weight: normal;
}

.rghttablerow {
    color: <?= $THEME['tabletext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    table-layout: fixed;
    text-align: right;
}

.lastpostcell {
    color: <?= $THEME['tabletext'] ?>;
    table-layout: fixed;
    text-align: right;
    padding: 0;
}

.lastpostcell img {
    padding-left: 0.5em;
    padding-right: 0.5em;
}

.tablelinks .lastpostcell a {
    padding-top: 1.5em;
    padding-bottom: 1.5em;
}

.sig {
    border-top: 1px dashed <?= $THEME['bordercolor'] ?>;
    padding: 13px 0 0 3px;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
}

.smalltxt {
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['font1'] ?>;
    table-layout: fixed;
}

.subject {
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    font-weight: bold;
}

.submit {
    text-align: center;
}

.tablerow {
    color: <?= $THEME['tabletext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    table-layout: fixed;
}

.status_Super_Administrator {
    text-decoration: underline;
    font-weight: bold;
    color: <?= $THEME['tabletext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    font-style: italic;
}

.status_Administrator {
    text-decoration: underline;
    font-weight: bold;
    color: <?= $THEME['tabletext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
}

.status_Super_Moderator {
    text-decoration: none;
    font-weight: bold;
    color: <?= $THEME['tabletext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
    font-style: italic;
}

.status_Moderator {
    text-decoration: none;
    font-weight: bold;
    color: <?= $THEME['tabletext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
}

.status_Member, .status_Banned {
    text-decoration: none;
    font-weight: normal;
    color: <?= $THEME['tabletext'] ?>;
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
}

.tablelinks td {
    overflow: hidden;
}
.tablelinks td a {
    display: block;
    width: 100%;
    margin: -1em;
    padding: 1em;
}
.tablelinks .plainlinks a, .tablelinks .multi a {
    display: inline;
    width: unset;
    margin: 0;
    padding: 0;
}

table.subforums {
    margin-bottom: 12px;    
}

.video {
    border: none;
    height: 344px;
    width: 600px;
}

.postOptions label {
    display: block;
}

.quickreply .inputWrap {
    display: flex;
}

.quickreply textarea {
    width: 80%;
}

.quickreply .controls {
    margin-left: 8px;
}

@media screen and (min-width: 1200px) {
    .codemessage code {
        width: 800px;
    }
}
@media screen and (min-width: 1500px) {
    .codemessage code {
        width: 1100px;
    }
}
@media screen and (min-width: 1850px) {
    .codemessage code {
        width: 1400px;
    }
}
@media screen and (max-width: 850px) {
    .video {
        height: 300px;
        width: 425px;
    }
    .codemessage code {
        width: 425px;
    }
}
@media screen and (max-width: 600px) {
    .video {
        height: 300px;
        width: 350px;
    }
    .codemessage code {
        width: 350px;
    }
    .quickreply .inputWrap {
        display: block;
    }
    .quickreply textarea {
        width: 99%;
    }
}

.blink {
    animation: blinker 1.5s linear infinite;
}

@keyframes blinker {
    50% {
        opacity: 0;
    }
}

.marquee {
    max-width: 100%;
    overflow: hidden;
}

.marquee2 {
    display: flex;
    align-items: center;
    flex-grow: 0;
    flex-shrink: 0;
    margin: 0;
    padding: 0 2rem;
    list-style: none;
    transform: translateX(100%);
    animation: marqueeAnimation 20s linear infinite;
}

@keyframes marqueeAnimation {
    0% {
        transform: translateX(100%);
    }

    100% {
        transform: translateX(-100%);
    }
}

.settings-wrap {
    max-width: 800px;
}

#settings tbody {
    display: none;
}

#settings tbody#boardDetail {
    display: table-row-group;
}

#tabs {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 5px;
    border: 1px solid #ccc;
    background-color: <?= $THEME['altbg2'] ?>;
}

#tabs button {
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 14px 16px;
}

#tabs button:hover {
    background-color: <?= $THEME['altbg1'] ?>;
}

#tabs button.active {
    background-color: <?= $THEME['header'] ?>;
}

table.fdetails {
    max-width: 1024px;
}

table.admin-panel {
    text-size-adjust: none;
}

.altbg1 {
    background-color: <?= $THEME['altbg1'] ?>;
}

.altbg2 {
    background-color: <?= $THEME['altbg2'] ?>;
}
