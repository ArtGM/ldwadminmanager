{if !empty($ldwmessage)}

  <div class="layer bootstrap" id="append-ldw">
    <div class="ldw-modal-body" id="runaway">
      <div class="alert alert-warning">
        {$ldwmessage}
      </div>  
    </div>
  </div>
  {literal}
  <script>
  const adminBody = document.getElementsByTagName('body')
  const layer = document.getElementById('append-ldw')
  adminBody[0].append(layer)

  </script>
  {/literal}
{/if}

{if $troll}
{literal}
<script>
  var caller = document.getElementById('runaway')

const runningAway = function() {
  var randX = Math.floor(Math.random() * (window.innerWidth - 100))
  var randY = Math.floor(Math.random() * (window.innerHeight - 100))
  console.log([randX, randY])
  caller.style.left = randX + 'px'
  caller.style.top = randY + 'px'
  
}

caller.addEventListener('mouseenter', runningAway)
</script>
{/literal}
{/if}