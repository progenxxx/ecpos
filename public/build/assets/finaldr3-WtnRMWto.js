import{D as k,Q as K,d as V,s as D,T as Q,p as $,c as L,w as v,o as n,f as N,a as r,b as x,g as b,i as q,j as O,x as J,u as T,e as p,h as S,t as a,F as f,v as m,n as z}from"./app-DfIvzsOQ.js";import{_ as W,a as X}from"./Update-Deozbwrp.js";import{_ as M}from"./Modal.vue_vue_type_style_index_0_scoped_fd08cd24_lang-C388c1rV.js";import{_ as Z}from"./TransparentButton-_-HDhpAm.js";import{S as tt}from"./SearchColored-C1auhzpt.js";import{_ as rt}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{_ as et}from"./AdminPanel-BfHVSfXn.js";import"./Modal-CysVaCy0.js";import"./InputError-CglVpcef.js";import"./FormComponent-DEXnV-6b.js";import"./RetailGroup-B2i0gpkm.js";import"./Logout-3bWQyGlF.js";/* empty css                                                             */import"./RetailItems-COxcnIKG.js";import"./Attendance-DnkoFQCQ.js";const st={class:"flex justify-start mb-4"},ot={class:"rounded-md shadow-lg bg-blue-100 border-blue-900 ml-2 mr-2 pb-2"},at=["value"],lt={class:"ml-2"},dt={class:"relative ml-2"},nt=["placeholder"],bt={role:"tablist",class:"tabs tabs-lifted mt-4 p-5"},pt={role:"tabpanel",class:"tab-content !bg-gray-100 !border-gray-200 p-6 h-[85vh] overflow-y-auto"},ut={key:0,class:"col-span-full text-center mt-8"},it={key:1,class:"col-span-full text-center mt-8"},ct={class:"text-xl font-bold mb-4"},gt={class:"max-w-4xl mx-auto p-4 bg-white shadow-lg text-xs"},Et={class:"w-full border-collapse border border-gray-400"},yt={colspan:"2",class:"border border-gray-400 p-1"},Ct={class:"font-bold"},Tt={colspan:"3",class:"border border-gray-400 p-1"},ft={class:"font-bold"},At={class:"border border-gray-400 p-1"},Dt={class:"border border-gray-400 p-1 text-center"},xt={class:"border border-gray-400 p-1 text-center"},Ot={class:"border border-gray-400 p-1 text-right"},Rt={class:"border border-gray-400 p-1 text-right"},vt={class:"border border-gray-400 p-1 text-right font-bold"},St={class:"border border-gray-400 p-1 text-center"},mt={class:"border border-gray-400 p-1 text-center"},It={class:"border border-gray-400 p-1 text-right"},Nt={class:"border border-gray-400 p-1 text-right"},Lt={class:"border border-gray-400 p-1 text-right font-bold"},Ut={colspan:"1",class:"border border-gray-400 p-1 text-center"},ht={colspan:"4",class:"border border-gray-400 p-1 text-center"},wt={class:"border border-gray-400 p-1 text-center"},kt={class:"border border-gray-400 p-1 text-right font-bold"},Vt={colspan:"3",class:"border border-gray-400 p-1"},$t={class:"font-bold"},Mt={colspan:"3",class:"border border-gray-400 p-1"},Pt={class:"font-bold"},Yt={colspan:"1",class:"border border-gray-400 p-2"},Gt={class:"flex justify-between items-center"},_t={class:"mr-2"},Bt=["onChange"],Ht={colspan:"1",class:"border border-gray-400 p-2"},jt={class:"flex justify-between items-center"},Ft={class:"mr-2"},Kt=["onChange"],Qt={colspan:"2",class:"border border-gray-400 p-2"},qt={class:"flex justify-between items-center"},Jt={class:"mr-2"},zt=["onChange"],Wt={colspan:"2",class:"border border-gray-400 p-2"},Xt={class:"flex justify-between items-center"},Zt={class:"mr-2"},tr=["onChange"],rr={__name:"finaldr3",props:{sptrans:{type:Array,required:!0},rbostoretables:{type:Array,required:!0},routes:{type:String,required:!0},getstore:{type:String,required:!0},partycakes:{type:[Array,Object],required:!0}},setup(C){k.defaults.headers.common["X-CSRF-TOKEN"]=document.querySelector('meta[name="csrf-token"]').getAttribute("content");const P=K(),l=V(Object.entries(P.props.groupedPicklist).reduce((s,[t,d])=>(s[t]=d.map(y=>({...y,actual:y.ACTUAL})),s),{})),g=C,Y=D(()=>Object.keys(l.value));D(()=>{const s=Object.values(l.value)[0];return s||[]});const A=s=>new Date(s).toLocaleDateString(),u=s=>new Intl.NumberFormat("en-PH",{style:"currency",currency:"PHP"}).format(s),U=s=>s.reduce((t,d)=>t+Number(d.CHECKINGCOUNT||0)*Number(d.COST||0),0),G=s=>s.reduce((t,d)=>t+Number(d.COUNTED||0)*Number(d.COST||0),0),_=s=>s.reduce((t,d)=>t+Number(d.SRP||0),0),E=Q({EndDate:"",STORE:""}),B=()=>{E.get(route("fdr.getrange"),{preserveState:!0,preserveScroll:!0,onSuccess:()=>{nextTick(()=>{const s=document.querySelector('[role="tabpanel"]');s&&(s.style.display="none",setTimeout(()=>{s.style.display=""},0))})}})};D(()=>{const s=E.StartDate;if(s){const t=new Date(s),d=t.getFullYear(),y=String(t.getMonth()+1).padStart(2,"0"),e=String(t.getDate()).padStart(2,"0");return`${d}-${y}-${e}`}return""}),D(()=>Object.values(l.value).flat().reduce((s,t)=>s+Number(t.COST||0)*Number(t.CHECKINGCOUNT||0),0));const h=D(()=>Object.values(l.value).flat().find(s=>Number(s.SPECIALORDER||0)>0)||{});D(()=>Number(h.value.COST||0)*Number(h.value.SPECIALORDER||0));const H=s=>{const t=parseFloat(s);return Number.isInteger(t)?t.toString():Math.round(t).toString()},i=V({orangeCrates:0,blueCrates:0,empanadaCrates:0,box:0}),I=async(s,t)=>{try{const d=await k.post("/api/update-crates-counts",{journalId:t,orangeCrates:i.value.orangeCrates,blueCrates:i.value.blueCrates,empanadaCrates:i.value.empanadaCrates,box:i.value.box});d.data.success?console.log("Crates counts updated successfully"):console.error("Failed to update crates counts",d.data)}catch(d){console.error("Error updating crates counts:",d.message)}},j=()=>{location.reload()},F=()=>{const s=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),t=Object.entries(l.value);let d="";t.forEach(([y,e])=>{const o=e.map(c=>`
      <tr>
        <td class="border p-1">${c.ITEMNAME}</td>
        <td class="border p-1 text-center">${H(c.CHECKINGCOUNT)}</td>
        <td class="border p-1 text-center">0</td>
        <td class="border p-1 text-center">0</td>
        <td class="border p-1 text-right">${u(c.COST)}</td>
        <td class="border p-1 text-right">${u(c.COST*c.CHECKINGCOUNT)}</td>
      </tr>
    `).join(""),R=U(e);let w="";y===g.getstore&&g.sptrans&&g.sptrans.length>0&&(w=`
        <tr>
          <td colspan="6" class="border p-1 font-bold">SPECIAL ORDERS</td>
        </tr>
        <tr>
          <td class="border p-1">PROMO</td>
          <td class="border p-1 text-center">DELIVERED QUANTITY</td>
          <td class="border p-1 text-center">RECEIVED QUANTITY</td>
          <td class="border p-1 text-center">VARIANCE</td>
          <td class="border p-1 text-right">UNIT COST</td>
          <td class="border p-1 text-right">AMOUNT</td>
        </tr>
        ${g.sptrans.map(c=>`
          <tr>
            <td class="border p-1 text-center">${c.ITEMNAME}</td>
            <td class="border p-1 text-center">${c.COUNTED}</td>
            <td class="border p-1 text-center">0</td>
            <td class="border p-1 text-center">0</td> 
            <td class="border p-1 text-right">${u(c.COST)}</td>
            <td class="border p-1 text-right">${u(c.COST*c.COUNTED)}</td>
          </tr>
        `).join("")}
      `),d+=`
      <div class="page-container">
        <div class="store-section">
          <table>
            <tr>
              <td colspan="6" class="text-center font-bold text-lg border p-1">
                MALIWALO<br>
                TARLAC CITY
              </td>
            </tr>
            <tr>
              <td colspan="4" class="font-bold border p-1">DELIVERY GOODS RECEIPT<br>BW PRODUCT</td>
              <td colspan="2" class="border p-1">
                DR #: <span class="font-bold">${e[0].JOURNALID}</span><br>
                DELIVERY DATE: ${A(e[0].DELIVERYDATE)}
              </td>
            </tr>
            <tr>
              <td colspan="3" class="border p-1">RECEIVED FROM:<br><span class="font-bold">HEADOFFICE</span></td>
              <td colspan="3" class="border p-1">DELIVERED TO:<br><span class="font-bold">${y}</span></td>
            </tr>
            <tr class="bg-gray-200 font-bold">
              <td class="border p-1">PRODUCT DESCRIPTION</td>
              <td class="border p-1 text-center">DELIVERED QUANTITY</td>
              <td class="border p-1 text-center">RECEIVED QUANTITY</td>
              <td class="border p-1 text-center">VARIANCE</td>
              <td class="border p-1 text-right">UNIT COST</td>
              <td class="border p-1 text-right">TOTAL</td>
            </tr>
            ${o}
            ${w}
            <tr>
              <td colspan="5" class="border p-1 text-right font-bold">TOTAL</td>
              <td class="border p-1 text-right font-bold">${u(R)}</td>
            </tr>
            <tr>
              <td colspan="3" class="border p-1">
                ENDORSED BY:DISPATCHING<br>
                <span class="font-bold">${e[0].DISPATCHER} | ${A(e[0].DELIVERYDATE)}</span><br>
                BREADS/CAKES<br>
                NAME & SIGNATURE/ DATE
              </td>
              <td colspan="3" class="border p-1">
                <span class="font-bold">${e[0].LOGISTICS} | ${A(e[0].DELIVERYDATE)}</span><br>
                DELIVERED BY:LOGISTICS<br>
                NAME & SIGNATURE/ DATE
              </td>
            </tr>
            <tr>
              <td colspan="6" class="border p-1 font-bold">CRATES QUANTITY DELIVERED</td>
            </tr>
            <tr>
              <td colspan="1" class="border p-1">ORANGE CRATES - ${e[0].orangeCrates}</td>
              <td colspan="1" class="border p-1">BLUE CRATES - ${e[0].blueCrates}</td>
              <td colspan="2" class="border p-1">EMPANADA CRATES - ${e[0].empanadaCrates}</td>
              <td colspan="3" class="border p-1">BOX - ${e[0].box}</td>
            </tr>
          </table>
        </div>
      </div>
    `}),s.document.write(`
    <html>
      <head>
        <title>Delivery Goods Receipt</title>
        <style>
          @page {
            size: legal portrait;
            margin: 0;
          }
          body { 
            font-family: Arial, sans-serif; 
            font-size: 12px;
            margin: 0;
            padding: 0;
          }
          .page-container {
            width: 100%;
            height: 100vh;
            page-break-after: always;
            padding: 10mm;
            box-sizing: border-box;
          }
          .store-section { 
            width: 100%; 
          }
          table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5mm;
          }
          th, td { 
            border: 1px solid black; 
            padding: 2px; 
            font-size: 12px; 
          }
          .text-center { text-align: center; }
          .text-right { text-align: right; }
          .font-bold { font-weight: bold; }
          .bg-gray-200 { background-color: #edf2f7; }
        </style>
      </head>
      <body>
        ${d}
      </body>
    </html>
  `),s.document.close(),s.focus(),s.print(),s.close()};return(s,t)=>{const d=$("InputLabel"),y=$("InputError");return n(),L(et,{"active-tab":"FINALDR"},{modals:v(()=>[s.showCreateModal?(n(),L(W,{key:0,onToggleActive:s.createModalHandler},null,8,["onToggleActive"])):N("",!0),s.showModalUpdate?(n(),L(X,{key:1,ID:s.id,SUBJECT:s.subject,DESCRIPTION:s.description,onToggleActive:s.updateModalHandler},null,8,["ID","SUBJECT","DESCRIPTION","onToggleActive"])):N("",!0)]),main:v(()=>[r("div",st,[x(M,{type:"button",onClick:F,class:"bg-navy text-white ml-4 mt-4 rounded-md text-sm"},{default:v(()=>t[7]||(t[7]=[b(" PRINT ")])),_:1}),x(M,{type:"button",onClick:j,class:"ml-2 bg-navy mt-4"},{default:v(()=>t[8]||(t[8]=[b(" RELOAD ")])),_:1}),r("div",ot,[r("form",{onSubmit:q(B,["prevent"]),class:"flex items-center mt-4"},[r("input",{type:"hidden",name:"_token",value:s.$page.props.csrf_token},null,8,at),r("div",lt,[x(d,{for:"STORE",value:"STORE",class:"sr-only"}),O(r("select",{id:"STORE","onUpdate:modelValue":t[0]||(t[0]=e=>T(E).STORE=e),class:"input input-bordered w-64 !bg-gray-100"},[t[9]||(t[9]=r("option",{disabled:"",value:""},"Select Store",-1)),(n(!0),p(f,null,S(C.rbostoretables,e=>(n(),p("option",{key:e.STOREID},a(e.NAME),1))),128))],512),[[J,T(E).STORE]])]),r("div",dt,[t[10]||(t[10]=r("div",{class:"flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none"},[r("svg",{class:"w-5 h-5 text-gray-500 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[r("path",{"fill-rule":"evenodd",d:"M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z","clip-rule":"evenodd"})])],-1)),O(r("input",{id:"EndDate",type:"date","onUpdate:modelValue":t[1]||(t[1]=e=>T(E).EndDate=e),onInput:t[2]||(t[2]=(...e)=>s.formattedDate2&&s.formattedDate2(...e)),placeholder:s.formattedDate2,class:"bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",required:""},null,40,nt),[[m,T(E).EndDate]]),x(y,{message:T(E).errors.EndDate,class:"mt-2"},null,8,["message"])]),x(Z,{type:"submit",disabled:T(E).processing,class:z({"opacity-25":T(E).processing})},{default:v(()=>[x(tt,{class:"h-8"})]),_:1},8,["disabled","class"])],32)])]),r("div",bt,[t[46]||(t[46]=r("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab !bg-gray-100 !text-gray-500 !font-bold","aria-label":"FINAL DR",checked:""},null,-1)),r("div",pt,[T(E).processing?(n(),p("div",ut,t[11]||(t[11]=[r("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):!l.value||Object.keys(l.value).length===0?(n(),p("div",it,t[12]||(t[12]=[r("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[r("p",{class:"text-gray-600 text-base sm:text-lg"},"No DR2 List Available")],-1)]))):(n(!0),p(f,{key:2},S(Y.value,e=>(n(),p("div",{key:e,class:"mb-8"},[r("h2",ct,a(e),1),r("div",gt,[r("table",Et,[t[43]||(t[43]=r("tr",null,[r("td",{colspan:"6",class:"text-center font-bold text-lg border border-gray-400 p-1"},[b(" MALIWALO"),r("br"),b(" TARLAC CITY ")])],-1)),r("tr",null,[t[15]||(t[15]=r("td",{colspan:"4",class:"font-bold border border-gray-400 p-1"},[b("DELIVERY GOODS RECEIPT"),r("br"),b("BW PRODUCT")],-1)),r("td",yt,[t[13]||(t[13]=b(" DR #: ")),r("span",Ct,a(l.value[e][0].JOURNALID),1),t[14]||(t[14]=r("br",null,null,-1)),b(" DELIVERY DATE: "+a(A(l.value[e][0].DELIVERYDATE)),1)])]),r("tr",null,[t[18]||(t[18]=r("td",{colspan:"3",class:"border border-gray-400 p-1"},[b("RECEIVED FROM:"),r("br"),r("span",{class:"font-bold"},"HEADOFFICE")],-1)),r("td",Tt,[t[16]||(t[16]=b("DELIVERED TO:")),t[17]||(t[17]=r("br",null,null,-1)),r("span",ft,a(e),1)])]),t[44]||(t[44]=r("tr",{class:"bg-gray-200 font-bold"},[r("td",{class:"border border-gray-400 p-1"},"PRODUCT DESCRIPTION"),r("td",{class:"border border-gray-400 p-1 text-center"},"DELIVERED QUANTITY"),r("td",{class:"border border-gray-400 p-1 text-center"},"RECEIVED QUANTITY"),r("td",{class:"border border-gray-400 p-1 text-center"},"VARIANCE"),r("td",{class:"border border-gray-400 p-1 text-right"},"UNIT COST"),r("td",{class:"border border-gray-400 p-1 text-right"},"TOTAL")],-1)),(n(!0),p(f,null,S(l.value[e],o=>(n(),p("tr",{key:o.ITEMID},[r("td",At,a(o.ITEMNAME),1),r("td",Dt,a(o.CHECKINGCOUNT),1),t[19]||(t[19]=r("td",{class:"border border-gray-400 p-1 text-center"},"0",-1)),r("td",xt,a(o.actual-o.CHECKINGCOUNT),1),r("td",Ot,a(u(o.COST)),1),r("td",Rt,a(u(o.COST*o.CHECKINGCOUNT)),1)]))),128)),r("tr",null,[t[20]||(t[20]=r("td",{colspan:"5",class:"border border-gray-400 p-1 text-right font-bold"},"TOTAL",-1)),r("td",vt,a(u(U(l.value[e]))),1)]),e===C.getstore&&g.sptrans&&g.sptrans.length>0?(n(),p(f,{key:0},[t[24]||(t[24]=r("tr",null,[r("td",{colspan:"6",class:"border border-gray-400 p-1 font-bold"},"SPECIAL ORDERS")],-1)),t[25]||(t[25]=r("tr",null,[r("td",{class:"border border-gray-400 p-1"},"PROMO"),r("td",{class:"border border-gray-400 p-1 text-center"},"DELIVERED QUANTITY"),r("td",{class:"border border-gray-400 p-1 text-center"},"RECEIVED QUANTITY"),r("td",{class:"border border-gray-400 p-1 text-center"},"VARIANCE"),r("td",{class:"border border-gray-400 p-1 text-right"},"UNIT COST"),r("td",{class:"border border-gray-400 p-1 text-right"},"AMOUNT")],-1)),(n(!0),p(f,null,S(g.sptrans,(o,R)=>(n(),p("tr",{key:R},[r("td",St,a(o.ITEMNAME),1),r("td",mt,a(o.COUNTED),1),t[21]||(t[21]=r("td",{class:"border border-gray-400 p-1 text-center"},"0",-1)),t[22]||(t[22]=r("td",{class:"border border-gray-400 p-1 text-center"},"0",-1)),r("td",It,a(u(o.COST)),1),r("td",Nt,a(u(o.COST*o.COUNTED)),1)]))),128)),r("tr",null,[t[23]||(t[23]=r("td",{colspan:"5",class:"border border-gray-400 p-1 text-right font-bold"},"TOTAL",-1)),r("td",Lt,a(u(G(g.sptrans))),1)])],64)):N("",!0),e===C.getstore&&C.partycakes&&(Array.isArray(C.partycakes)?C.partycakes.length>0:Object.keys(C.partycakes).length>0)?(n(),p(f,{key:1},[t[27]||(t[27]=r("tr",null,[r("td",{colspan:"6",class:"border border-gray-400 p-1 font-bold"},"PARTY CAKES ORDERS")],-1)),t[28]||(t[28]=r("tr",null,[r("td",{colspan:"1",class:"border border-gray-400 p-1"},"PARTYCAKES OS NO"),r("td",{colspan:"4",class:"border border-gray-400 p-1 text-center"},"DESIGN"),r("td",{class:"border border-gray-400 p-1 text-center"},"AMOUNT")],-1)),(n(!0),p(f,null,S(g.partycakes,(o,R)=>(n(),p("tr",{key:R},[r("td",Ut,a(o.COSNO),1),r("td",ht,a(o.BDAYCODENO),1),r("td",wt,a(o.SRP),1)]))),128)),r("tr",null,[t[26]||(t[26]=r("td",{colspan:"5",class:"border border-gray-400 p-1 text-right font-bold"},"TOTAL",-1)),r("td",kt,a(u(_(g.partycakes))),1)])],64)):N("",!0),r("tr",null,[r("td",Vt,[t[29]||(t[29]=b(" ENDORSED BY:DISPATCHING")),t[30]||(t[30]=r("br",null,null,-1)),r("span",$t,a(l.value[e][0].DISPATCHER)+" | "+a(A(l.value[e][0].DELIVERYDATE)),1),t[31]||(t[31]=r("br",null,null,-1)),t[32]||(t[32]=b(" BREADS/CAKES")),t[33]||(t[33]=r("br",null,null,-1)),t[34]||(t[34]=b(" NAME & SIGNATURE/ DATE "))]),r("td",Mt,[r("span",Pt,a(l.value[e][0].LOGISTICS)+" | "+a(A(l.value[e][0].DELIVERYDATE)),1),t[35]||(t[35]=r("br",null,null,-1)),t[36]||(t[36]=b(" DELIVERED BY:LOGISTICS")),t[37]||(t[37]=r("br",null,null,-1)),t[38]||(t[38]=b(" NAME & SIGNATURE/ DATE "))])]),t[45]||(t[45]=r("tr",null,[r("td",{colspan:"6",class:"border border-gray-400 p-1 font-bold"},"CRATES QUANTITY DELIVERED")],-1)),r("tr",null,[r("td",Yt,[r("div",Gt,[t[39]||(t[39]=r("span",null,"ORANGE CRATES",-1)),r("span",_t,a(l.value[e][0].orangeCrates),1),O(r("input",{type:"number","onUpdate:modelValue":t[3]||(t[3]=o=>i.value.orangeCrates=o),onChange:o=>I(e,l.value[e][0].JOURNALID),class:"w-16 px-2 py-1 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"},null,40,Bt),[[m,i.value.orangeCrates]])])]),r("td",Ht,[r("div",jt,[t[40]||(t[40]=r("span",null,"BLUE CRATES",-1)),r("span",Ft,a(l.value[e][0].blueCrates),1),O(r("input",{type:"number","onUpdate:modelValue":t[4]||(t[4]=o=>i.value.blueCrates=o),onChange:o=>I(e,l.value[e][0].JOURNALID),class:"w-16 px-2 py-1 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"},null,40,Kt),[[m,i.value.blueCrates]])])]),r("td",Qt,[r("div",qt,[t[41]||(t[41]=r("span",null,"EMPANADA CRATES",-1)),r("span",Jt,a(l.value[e][0].empanadaCrates),1),O(r("input",{type:"number","onUpdate:modelValue":t[5]||(t[5]=o=>i.value.empanadaCrates=o),onChange:o=>I(e,l.value[e][0].JOURNALID),class:"w-16 px-2 py-1 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"},null,40,zt),[[m,i.value.empanadaCrates]])])]),r("td",Wt,[r("div",Xt,[t[42]||(t[42]=r("span",null,"BOX",-1)),r("span",Zt,a(l.value[e][0].box),1),O(r("input",{type:"number","onUpdate:modelValue":t[6]||(t[6]=o=>i.value.box=o),onChange:o=>I(e,l.value[e][0].JOURNALID),class:"w-16 px-2 py-1 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"},null,40,tr),[[m,i.value.box]])])])])])])]))),128))])])]),_:1})}}},Cr=rt(rr,[["__scopeId","data-v-379753c0"]]);export{Cr as default};
