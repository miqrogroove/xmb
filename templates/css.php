/* XMB Stylesheet */
body {
    scrollbar-arrow-color: <?= $THEME['header'] ?>;
    scrollbar-base-color: <?= $THEME['altbg1'] ?>;
    text-align: left;
    <?= $THEME['bgcode'], "\n" ?>
    font-family: <?= $THEME['font'] ?>;
    font-size: <?= $THEME['fontsize'] ?>;
}

body > br {
    line-height: 18px;
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
    color: <?= $THEME['bordercolor'] ?>;
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
    font-weight: bold;
    height: 20px;
    padding-left: 5px;
}

td.quotemessage {
    background-color: <?= $THEME['altbg2'] ?>;
    color: <?= $THEME['tabletext'] ?>;
    padding: 10px 0 20px 5px;
}

textarea, select, input, object {
    background-color: <?= $THEME['altbg1'] ?>;
    border: 1px solid <?= $THEME['bordercolor'] ?>;
    color: <?= $THEME['tabletext'] ?>;
    font-family: Verdana, arial, helvetica, sans-serif;
    font-size: <?= $THEME['font-larger-1'] ?>;
    font-weight: normal;
    table-layout: fixed;
}

input[readonly] {
    background: none;
    border: none;
}

.category {
    <?= $THEME['catcss'] ?>
    table-layout: fixed;
    text-align: left;
}

.ctrcategory {
    <?= $THEME['catcss'] ?>
    table-layout: fixed;
    text-align: center;
}

.ctrtablerow {
    color: <?= $THEME['tabletext'] ?>;
    table-layout: fixed;
    text-align: center;
}

.header {
    background-color: <?= $THEME['header'] ?>;
    color: <?= $THEME['headertext'] ?>;
    font-size: <?= $THEME['font-smaller-1'] ?>;
    font-weight: bold;
    table-layout: fixed;
}

.header2 {
    background-color: <?= $THEME['altbg1'] ?>;
    font-weight: bold;
    table-layout: fixed;
}

.mediumtxt {
    color: <?= $THEME['tabletext'] ?>;
    font-weight: normal;
    table-layout: fixed;
}

.multi {
    table-layout: fixed;
}

.nav {
    font-weight: bold;
    table-layout: fixed;
}

.navtd {
    background-color: <?= $THEME['header'] ?>;
    color: <?= $THEME['headertext'] ?>;
    table-layout: fixed;
    text-decoration: none;
}

.navtd a {
    white-space: nowrap;
}

.navtd2 {
    background-color: <?= $THEME['header'] ?>;
    color: <?= $THEME['headertext'] ?>;
    font-size: <?= $THEME['font-smaller-2'] ?>;
    table-layout: fixed;
    text-decoration: none;
}

.post {
    font-size: <?= $THEME['font-larger-2'] ?>;
    font-weight: normal;
}

.rghttablerow {
    color: <?= $THEME['tabletext'] ?>;
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
}

.smalltxt {
    font-size: <?= $THEME['font-smaller-1'] ?>;
    table-layout: fixed;
}

.subject {
    font-weight: bold;
}

.submit {
    text-align: center;
}

.tablerow {
    color: <?= $THEME['tabletext'] ?>;
    table-layout: fixed;
}

span[class^="status_"] {
    color: <?= $THEME['tabletext'] ?>;
    font-size: <?= $THEME['font-smaller-1'] ?>;
}

.status_Super_Administrator {
    text-decoration: underline;
    font-weight: bold;
    font-style: italic;
}

.status_Administrator {
    text-decoration: underline;
    font-weight: bold;
}

.status_Super_Moderator {
    text-decoration: none;
    font-weight: bold;
    font-style: italic;
}

.status_Moderator {
    text-decoration: none;
    font-weight: bold;
}

.status_Member, .status_Banned {
    text-decoration: none;
    font-weight: normal;
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

.medium-width-box {
    min-width: 50%;
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
    .medium-width-box {
        min-width: 925px;
        max-width: 1000px;
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
